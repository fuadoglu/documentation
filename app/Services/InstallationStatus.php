<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Schema;

class InstallationStatus
{
    public function isInstalled(): bool
    {
        if (app()->runningUnitTests() && ! config('install.enforce_during_tests', false)) {
            return true;
        }

        if (is_file($this->lockFilePath())) {
            return true;
        }

        try {
            if (! Schema::hasTable('users')) {
                return false;
            }

            return User::query()->exists();
        } catch (\Throwable) {
            return false;
        }
    }

    public function markInstalled(array $meta = []): void
    {
        $lockFilePath = $this->lockFilePath();
        $directory = dirname($lockFilePath);

        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $payload = [
            'installed_at' => now()->toIso8601String(),
            'app_url' => (string) config('app.url'),
            'meta' => $meta,
        ];

        file_put_contents(
            $lockFilePath,
            json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
        );
    }

    /**
     * @return array<string, array<int, array{label: string, ok: bool}>>
     */
    public function requirements(): array
    {
        $requiredPhpVersion = '8.2.0';
        $requiredExtensions = [
            'bcmath',
            'ctype',
            'fileinfo',
            'json',
            'mbstring',
            'openssl',
            'pdo',
            'pdo_mysql',
            'tokenizer',
            'xml',
        ];

        $extensions = array_map(
            fn (string $extension): array => [
                'label' => __('ui.setup.requirement_extension', ['extension' => $extension]),
                'ok' => extension_loaded($extension),
            ],
            $requiredExtensions
        );

        return [
            'system' => [
                [
                    'label' => __('ui.setup.requirement_php_version', [
                        'required' => $requiredPhpVersion,
                        'current' => PHP_VERSION,
                    ]),
                    'ok' => version_compare(PHP_VERSION, $requiredPhpVersion, '>='),
                ],
            ],
            'extensions' => $extensions,
            'permissions' => [
                [
                    'label' => __('ui.setup.requirement_env_file'),
                    'ok' => $this->isEnvFileWritable(),
                ],
                [
                    'label' => __('ui.setup.requirement_storage'),
                    'ok' => $this->isDirectoryWritable(storage_path()),
                ],
                [
                    'label' => __('ui.setup.requirement_bootstrap_cache'),
                    'ok' => $this->isDirectoryWritable(base_path('bootstrap/cache')),
                ],
            ],
        ];
    }

    public function allRequirementsPassed(): bool
    {
        foreach ($this->requirements() as $group) {
            foreach ($group as $check) {
                if (! $check['ok']) {
                    return false;
                }
            }
        }

        return true;
    }

    private function lockFilePath(): string
    {
        return storage_path((string) config('install.lock_file', 'app/installed.lock'));
    }

    private function isEnvFileWritable(): bool
    {
        $envPath = base_path('.env');

        if (is_file($envPath)) {
            return is_writable($envPath);
        }

        return is_writable(base_path());
    }

    private function isDirectoryWritable(string $path): bool
    {
        return is_dir($path) && is_writable($path);
    }
}

