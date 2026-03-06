<?php

namespace App\Http\Controllers\Install;

use App\Http\Controllers\Controller;
use App\Http\Requests\Install\RunSetupRequest;
use App\Services\InstallationStatus;
use App\Services\SetupWizardService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SetupWizardController extends Controller
{
    public function __construct(
        private readonly InstallationStatus $installationStatus,
        private readonly SetupWizardService $setupWizardService,
    ) {
    }

    public function index(Request $request): View|RedirectResponse
    {
        if ($this->installationStatus->isInstalled()) {
            return redirect()->route('login');
        }

        $urlHost = (string) parse_url($request->getSchemeAndHttpHost(), PHP_URL_HOST);
        $normalizedHost = trim(Str::replaceFirst('www.', '', Str::lower($urlHost)));
        $defaultDomain = $normalizedHost !== '' && str_contains($normalizedHost, '.')
            ? $normalizedHost
            : 'company.az';

        $defaults = [
            'app_name' => (string) config('app.name', 'ECO DC'),
            'company_name' => (string) config('app.name', 'ECO DC'),
            'app_url' => rtrim($request->getSchemeAndHttpHost().$request->getBaseUrl(), '/'),
            'app_locale' => (string) config('app.locale', 'az'),
            'app_timezone' => (string) config('app.timezone', 'Asia/Baku'),
            'allowed_login_domain' => $defaultDomain,
            'db_host' => (string) env('DB_HOST', 'localhost'),
            'db_port' => (string) env('DB_PORT', '3306'),
            'db_database' => (string) env('DB_DATABASE', ''),
            'db_username' => (string) env('DB_USERNAME', ''),
            'db_password' => '',
            'admin_name' => 'System Admin',
            'admin_email' => 'admin@'.$defaultDomain,
        ];

        return view('install.wizard', [
            'defaults' => $defaults,
            'requirements' => $this->installationStatus->requirements(),
            'allRequirementsMet' => $this->installationStatus->allRequirementsPassed(),
        ]);
    }

    public function store(RunSetupRequest $request): RedirectResponse
    {
        if ($this->installationStatus->isInstalled()) {
            return redirect()->route('login');
        }

        try {
            $this->setupWizardService->install($request->validated());
        } catch (\Throwable $exception) {
            report($exception);

            $message = app()->environment('local')
                ? $exception->getMessage()
                : (string) __('ui.setup.install_failed');

            return back()
                ->withInput($request->except(['admin_password', 'admin_password_confirmation', 'db_password']))
                ->withErrors(['setup' => $message]);
        }

        return redirect()->route('login')->with('status', __('ui.setup.install_success'));
    }
}

