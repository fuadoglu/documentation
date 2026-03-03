<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class LocaleController extends Controller
{
    public function update(Request $request): RedirectResponse
    {
        $availableLocales = config('app.available_locales', ['az', 'en']);
        if ($availableLocales === []) {
            $availableLocales = ['az', 'en'];
        }

        $validated = $request->validate([
            'locale' => ['required', Rule::in($availableLocales)],
        ]);

        $locale = $validated['locale'];

        $request->session()->put('locale', $locale);

        if ($request->user()) {
            $request->user()->forceFill([
                'locale' => $locale,
            ])->save();
        }

        app()->setLocale($locale);

        return back();
    }
}
