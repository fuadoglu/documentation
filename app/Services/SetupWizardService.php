<?php

namespace App\Services;

use App\Models\BrandingSetting;
use App\Models\User;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use RuntimeException;
use Spatie\Permission\Models\Role;

class SetupWizardService
{
    public function __construct(
        private readonly InstallationStatus $installationStatus,
    ) {
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function install(array $data): void
    {
        if (! $this->installationStatus->allRequirementsPassed()) {
            throw new RuntimeException((string) __('ui.setup.requirements_not_met'));
        }

        $appUrl = $this->normalizeAppUrl((string) $data['app_url']);
        $sessionPath = $this->sessionPathFromUrl($appUrl);
        $isSecureUrl = str_starts_with(Str::lower($appUrl), 'https://');

        $appKey = (string) (config('app.key') ?: '');
        if ($appKey === '') {
            $appKey = 'base64:'.base64_encode(random_bytes(32));
        }

        $envUpdates = [
            'APP_NAME' => (string) $data['app_name'],
            'APP_ENV' => 'production',
            'APP_DEBUG' => 'false',
            'APP_URL' => $appUrl,
            'ASSET_URL' => $appUrl,
            'APP_KEY' => $appKey,
            'APP_LOCALE' => (string) $data['app_locale'],
            'APP_TIMEZONE' => (string) $data['app_timezone'],
            'DB_CONNECTION' => 'mysql',
            'DB_HOST' => (string) $data['db_host'],
            'DB_PORT' => (string) $data['db_port'],
            'DB_DATABASE' => (string) $data['db_database'],
            'DB_USERNAME' => (string) $data['db_username'],
            'DB_PASSWORD' => (string) $data['db_password'],
            'ALLOWED_LOGIN_DOMAIN' => (string) $data['allowed_login_domain'],
            'ADMIN_EMAIL' => (string) $data['admin_email'],
            'ADMIN_PASSWORD' => (string) $data['admin_password'],
            'SESSION_PATH' => $sessionPath,
            'SESSION_SECURE_COOKIE' => $isSecureUrl ? 'true' : 'false',
        ];

        $this->assertDatabaseConnection($envUpdates);

        $this->ensureEnvFileExists();
        $this->updateEnvFile($envUpdates);
        $this->applyRuntimeConfiguration($envUpdates);

        Artisan::call('migrate', ['--force' => true]);
        Artisan::call('db:seed', ['--force' => true]);

        $this->finalizeBusinessSettings($data);
        $this->writeSetupAuditEvent($data);

        // Never keep plaintext installer password in .env after first setup.
        $this->updateEnvFile([
            'ADMIN_PASSWORD' => '',
            'INSTALLER_ENABLED' => 'false',
        ]);
        config(['install.enabled' => false]);

        Artisan::call('storage:link');
        Artisan::call('optimize:clear');
        Artisan::call('config:cache');
        Artisan::call('view:cache');

        $cleanupSuccess = $this->cleanupWizardFiles();

        $this->installationStatus->markInstalled([
            'company_name' => (string) $data['company_name'],
            'admin_email' => (string) $data['admin_email'],
            'wizard_cleanup' => $cleanupSuccess ? 'deleted' : 'disabled',
        ]);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function finalizeBusinessSettings(array $data): void
    {
        $branding = BrandingSetting::current();
        $branding->forceFill([
            'company_name' => (string) $data['company_name'],
            'allowed_login_domain' => (string) $data['allowed_login_domain'],
            'timezone' => (string) $data['app_timezone'],
        ])->save();

        $admin = User::query()->firstOrNew(['email' => (string) $data['admin_email']]);
        $admin->forceFill([
            'name' => (string) $data['admin_name'],
            'password' => Hash::make((string) $data['admin_password']),
            'locale' => (string) $data['app_locale'],
            'is_active' => true,
            'must_change_password' => false,
        ])->save();

        $adminRole = Role::query()->firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $admin->syncRoles([$adminRole->name]);

        Cache::forget(BrandingSetting::CACHE_KEY);
    }

    /**
     * @param  array<string, string>  $envUpdates
     */
    private function assertDatabaseConnection(array $envUpdates): void
    {
        $this->applyRuntimeConfiguration($envUpdates);

        DB::purge('mysql');
        DB::connection('mysql')->getPdo();
    }

    /**
     * @param  array<string, string>  $envUpdates
     */
    private function applyRuntimeConfiguration(array $envUpdates): void
    {
        config([
            'app.name' => $envUpdates['APP_NAME'],
            'app.url' => $envUpdates['APP_URL'],
            'app.locale' => $envUpdates['APP_LOCALE'],
            'app.timezone' => $envUpdates['APP_TIMEZONE'],
            'app.key' => $envUpdates['APP_KEY'],
            'database.default' => 'mysql',
            'database.connections.mysql.host' => $envUpdates['DB_HOST'],
            'database.connections.mysql.port' => (int) $envUpdates['DB_PORT'],
            'database.connections.mysql.database' => $envUpdates['DB_DATABASE'],
            'database.connections.mysql.username' => $envUpdates['DB_USERNAME'],
            'database.connections.mysql.password' => $envUpdates['DB_PASSWORD'],
        ]);

        date_default_timezone_set($envUpdates['APP_TIMEZONE']);
    }

    private function ensureEnvFileExists(): void
    {
        $envPath = base_path('.env');
        if (is_file($envPath)) {
            return;
        }

        $examplePath = base_path('.env.example');
        if (! is_file($examplePath)) {
            throw new RuntimeException('Missing .env.example');
        }

        File::copy($examplePath, $envPath);
    }

    /**
     * @param  array<string, string>  $values
     */
    private function updateEnvFile(array $values): void
    {
        $envPath = base_path('.env');
        $content = File::get($envPath);

        foreach ($values as $key => $value) {
            $formatted = $this->formatEnvValue($value);
            $line = "{$key}={$formatted}";
            $pattern = "/^".preg_quote($key, '/')."=.*/m";

            if (preg_match($pattern, $content) === 1) {
                $content = preg_replace($pattern, $line, $content, 1) ?? $content;
            } else {
                $content = rtrim($content).PHP_EOL.$line.PHP_EOL;
            }
        }

        File::put($envPath, $content);
    }

    private function formatEnvValue(string $value): string
    {
        if ($value === '') {
            return '';
        }

        if (preg_match('/\s|#|=|"|\'/', $value) === 1) {
            return '"'.str_replace('"', '\"', $value).'"';
        }

        return $value;
    }

    private function sessionPathFromUrl(string $url): string
    {
        $rawPath = parse_url($url, PHP_URL_PATH);
        if (! is_string($rawPath) || $rawPath === '') {
            return '/';
        }

        $trimmed = trim($rawPath);
        if ($trimmed === '/' || $trimmed === '') {
            return '/';
        }

        return '/'.trim($trimmed, '/');
    }

    private function normalizeAppUrl(string $url): string
    {
        $trimmed = rtrim(trim($url), '/');
        if ($trimmed === '') {
            return $url;
        }

        $normalized = preg_replace('#/install(?:/.*)?$#i', '', $trimmed);
        if (! is_string($normalized) || $normalized === '') {
            return $trimmed;
        }

        return $normalized;
    }

    /**
     * Installer self-destruct is best-effort.
     * Fallback is environment-level disable with INSTALLER_ENABLED=false.
     */
    private function cleanupWizardFiles(): bool
    {
        if (! config('install.self_destruct', true)) {
            return false;
        }

        $files = [
            base_path('routes/install.php'),
            app_path('Http/Controllers/Install/SetupWizardController.php'),
            app_path('Http/Requests/Install/RunSetupRequest.php'),
            resource_path('views/install/wizard.blade.php'),
        ];

        $allDeleted = true;

        foreach ($files as $file) {
            if (! is_file($file)) {
                continue;
            }

            if (! is_writable($file) || ! @unlink($file)) {
                $allDeleted = false;
            }
        }

        $directories = [
            app_path('Http/Controllers/Install'),
            app_path('Http/Requests/Install'),
            resource_path('views/install'),
        ];

        foreach ($directories as $directory) {
            if (is_dir($directory) && count(File::files($directory)) === 0) {
                @rmdir($directory);
            }
        }

        return $allDeleted;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function writeSetupAuditEvent(array $data): void
    {
        try {
            if (! Schema::hasTable('activity_log')) {
                return;
            }

            activity()
                ->event('installed')
                ->withProperties([
                    'company_name' => (string) $data['company_name'],
                    'admin_email' => (string) $data['admin_email'],
                    'app_url' => (string) $data['app_url'],
                    'timezone' => (string) $data['app_timezone'],
                ])
                ->log(__('messages.audit.setup_completed'));
        } catch (\Throwable) {
            // setup must continue even if audit insert fails
        }
    }
}
