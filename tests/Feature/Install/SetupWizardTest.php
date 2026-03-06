<?php

namespace Tests\Feature\Install;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SetupWizardTest extends TestCase
{
    use RefreshDatabase;

    public function test_uninstalled_app_redirects_to_setup_wizard(): void
    {
        $this->enableInstallerChecks();

        $this->get('/')->assertRedirect('/install');
        $this->get('/install')
            ->assertOk()
            ->assertSeeText(__('ui.setup.title'));
    }

    public function test_installed_app_blocks_setup_route(): void
    {
        $this->enableInstallerChecks();

        User::factory()->create();

        $this->get('/install')->assertRedirect('/login');
    }

    public function test_installer_disabled_blocks_setup_and_returns_503_for_main_app(): void
    {
        $this->enableInstallerChecks();
        config(['install.enabled' => false]);

        $this->get('/install')->assertStatus(503);
        $this->get('/')->assertStatus(503);
    }

    private function enableInstallerChecks(): void
    {
        config([
            'install.enforce_during_tests' => true,
            'install.lock_file' => 'app/testing-installed.lock',
        ]);

        $lockFile = storage_path('app/testing-installed.lock');
        if (is_file($lockFile)) {
            @unlink($lockFile);
        }
    }
}
