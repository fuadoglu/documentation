<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BrandingSetting;
use App\Models\User;
use App\Support\AuditLogger;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    public function index(): View
    {
        return view('admin.users.index', [
            'users' => User::query()->with('roles')->orderByDesc('id')->paginate(20),
            'locales' => $this->availableLocales(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $availableLocales = $this->availableLocales();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'string', 'email', 'max:190', 'unique:users,email'],
            'password' => ['required', 'string', $this->strongPasswordRule(), 'confirmed'],
            'locale' => ['nullable', Rule::in($availableLocales)],
            'role' => ['required', Rule::in(['admin', 'employee'])],
            'is_active' => ['nullable', 'boolean'],
        ]);

        if (! $this->isAllowedDomain($validated['email'])) {
            return back()->withErrors([
                'email' => __('messages.validation.email_domain_mismatch'),
            ])->withInput();
        }

        $user = User::query()->create([
            'name' => $validated['name'],
            'email' => Str::lower($validated['email']),
            'locale' => $validated['locale'] ?? config('app.locale'),
            'password' => Hash::make($validated['password']),
            'is_active' => (bool) ($validated['is_active'] ?? true),
            'must_change_password' => true,
        ]);

        $user->syncRoles([$validated['role']]);

        AuditLogger::event($request, $user, 'created', __('messages.audit.user_created'));

        return redirect()->route('admin.users.index')->with('status', __('messages.status.user_created'));
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $availableLocales = $this->availableLocales();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'string', 'email', 'max:190', Rule::unique('users', 'email')->ignore($user->id)],
            'password' => ['nullable', 'string', $this->strongPasswordRule(), 'confirmed'],
            'locale' => ['nullable', Rule::in($availableLocales)],
            'role' => ['required', Rule::in(['admin', 'employee'])],
            'is_active' => ['nullable', 'boolean'],
        ]);

        if (! $this->isAllowedDomain($validated['email'])) {
            return back()->withErrors([
                'email' => __('messages.validation.email_domain_mismatch'),
            ])->withInput();
        }

        $payload = [
            'name' => $validated['name'],
            'email' => Str::lower($validated['email']),
            'locale' => $validated['locale'] ?? $user->locale,
            'is_active' => (bool) ($validated['is_active'] ?? false),
        ];

        if (! empty($validated['password'])) {
            $payload['password'] = Hash::make($validated['password']);
            $payload['must_change_password'] = true;
        }

        $user->update($payload);
        $user->syncRoles([$validated['role']]);

        AuditLogger::event($request, $user, 'updated', __('messages.audit.user_updated'));

        return redirect()->route('admin.users.index')->with('status', __('messages.status.user_updated'));
    }

    public function status(Request $request, User $user): RedirectResponse
    {
        if ($request->user()->is($user)) {
            return back()->withErrors([
                'status' => __('messages.error.cannot_deactivate_self'),
            ]);
        }

        $user->update(['is_active' => ! $user->is_active]);

        AuditLogger::event($request, $user, 'status', __('messages.audit.user_status_changed'));

        return redirect()->route('admin.users.index')->with('status', __('messages.status.user_status_changed'));
    }

    public function resetPassword(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'password' => ['required', 'string', $this->strongPasswordRule(), 'confirmed'],
        ]);

        $user->update([
            'password' => Hash::make($validated['password']),
            'must_change_password' => true,
        ]);

        AuditLogger::event($request, $user, 'reset_password', __('messages.audit.user_password_reset'));

        return redirect()->route('admin.users.index')->with('status', __('messages.status.user_password_reset'));
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        if ($request->user()->is($user)) {
            return back()->withErrors([
                'delete' => __('messages.error.cannot_delete_self'),
            ]);
        }

        $user->delete();

        AuditLogger::event($request, $user, 'deleted', __('messages.audit.user_deleted'));

        return redirect()->route('admin.users.index')->with('status', __('messages.status.user_deleted'));
    }

    private function isAllowedDomain(string $email): bool
    {
        $allowedDomain = BrandingSetting::current()->allowed_login_domain;

        if (! $allowedDomain) {
            return true;
        }

        return Str::lower((string) Str::after($email, '@')) === Str::lower($allowedDomain);
    }

    /**
     * @return array<int, string>
     */
    private function availableLocales(): array
    {
        $locales = config('app.available_locales', ['az', 'en']);

        return $locales === [] ? ['az', 'en'] : array_values($locales);
    }

    private function strongPasswordRule(): Password
    {
        return Password::min(12)->letters()->mixedCase()->numbers()->symbols();
    }
}
