<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BrandingSetting;
use App\Support\AuditLogger;
use DateTimeZone;
use Illuminate\Http\UploadedFile;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class BrandingController extends Controller
{
    public function edit(): View
    {
        return view('admin.branding.edit', [
            'settings' => BrandingSetting::current(),
            'timezones' => $this->timezoneOptions(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'company_name' => ['required', 'string', 'max:160'],
            'allowed_login_domain' => ['required', 'string', 'max:120', 'regex:/^[a-z0-9.-]+\.[a-z]{2,}$/i'],
            'attachments_enabled' => ['required', Rule::in(['0', '1'])],
            'primary_color' => ['nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'secondary_color' => ['nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'timezone' => ['nullable', 'timezone:all'],
            'logo' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,svg', 'max:2048'],
            'favicon' => ['nullable', 'file', 'mimes:ico,png,svg', 'max:1024'],
        ]);

        $settings = BrandingSetting::current();

        $this->ensureSafeSvg($request->file('logo'), 'logo');
        $this->ensureSafeSvg($request->file('favicon'), 'favicon');

        $payload = [
            'company_name' => $validated['company_name'],
            'allowed_login_domain' => strtolower($validated['allowed_login_domain']),
            'attachments_enabled' => (bool) $validated['attachments_enabled'],
            'primary_color' => $validated['primary_color'] ?? null,
            'secondary_color' => $validated['secondary_color'] ?? null,
            'timezone' => $validated['timezone'] ?? ($settings->timezone ?: config('app.timezone', 'UTC')),
        ];

        if ($request->hasFile('logo')) {
            if ($settings->logo_path) {
                Storage::disk('public')->delete($settings->logo_path);
            }

            $payload['logo_path'] = $request->file('logo')->store('branding', 'public');
        }

        if ($request->hasFile('favicon')) {
            if ($settings->favicon_path) {
                Storage::disk('public')->delete($settings->favicon_path);
            }

            $payload['favicon_path'] = $request->file('favicon')->store('branding', 'public');
        }

        $settings->update($payload);

        AuditLogger::event($request, $settings, 'updated', __('messages.audit.branding_updated'));

        return redirect()->route('admin.branding.edit')->with('status', __('messages.status.branding_updated'));
    }

    private function ensureSafeSvg(?UploadedFile $file, string $field): void
    {
        if (! $file) {
            return;
        }

        $extension = Str::lower($file->getClientOriginalExtension());
        $mime = Str::lower((string) $file->getClientMimeType());

        if ($extension !== 'svg' && ! str_contains($mime, 'svg')) {
            return;
        }

        $contents = file_get_contents($file->getRealPath());

        if (! is_string($contents) || trim($contents) === '') {
            throw ValidationException::withMessages([
                $field => __('messages.validation.unsafe_svg'),
            ]);
        }

        if (! preg_match('/<\s*svg\b/i', $contents)) {
            throw ValidationException::withMessages([
                $field => __('messages.validation.unsafe_svg'),
            ]);
        }

        $forbiddenPatterns = [
            '/<\s*script\b/i',
            '/\bon\w+\s*=/i',
            '/javascript\s*:/i',
            '/<\s*(iframe|object|embed|link|meta)\b/i',
            '/<\s*foreignobject\b/i',
            '/(?:xlink:)?href\s*=\s*["\']\s*(?:https?:|data:|javascript:)/i',
        ];

        foreach ($forbiddenPatterns as $pattern) {
            if (preg_match($pattern, $contents) === 1) {
                throw ValidationException::withMessages([
                    $field => __('messages.validation.unsafe_svg'),
                ]);
            }
        }
    }

    /**
     * @return array<int, string>
     */
    private function timezoneOptions(): array
    {
        $preferred = [
            'Asia/Baku',
            'UTC',
            'Europe/Istanbul',
            'Europe/London',
            'Europe/Berlin',
            'America/New_York',
            'America/Los_Angeles',
            'Asia/Dubai',
        ];

        return array_values(array_unique(array_merge($preferred, DateTimeZone::listIdentifiers())));
    }
}
