<?php

namespace App\Http\Requests;

use App\Models\BrandingSetting;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $allowedDomain = Str::lower(BrandingSetting::current()->allowed_login_domain);
        $availableLocales = config('app.available_locales', ['az', 'en']);
        if ($availableLocales === []) {
            $availableLocales = ['az', 'en'];
        }

        return [
            'name' => ['required', 'string', 'max:150'],
            'locale' => ['nullable', Rule::in($availableLocales)],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:190',
                Rule::unique(User::class)->ignore($this->user()->id),
                function (string $attribute, mixed $value, \Closure $fail) use ($allowedDomain): void {
                    $domain = Str::lower((string) Str::after((string) $value, '@'));

                    if ($allowedDomain && $domain !== $allowedDomain) {
                        $fail(__('messages.validation.profile_domain_mismatch'));
                    }
                },
            ],
        ];
    }
}
