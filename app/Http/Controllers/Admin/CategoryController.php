<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Support\AuditLogger;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    public function index(): View
    {
        return view('admin.categories.index', [
            'categories' => Category::query()->orderBy('sort_order')->orderBy('name')->paginate(20),
            'locales' => $this->availableLocales(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $availableLocales = $this->availableLocales();

        $validated = $request->validate([
            'code' => ['required', 'string', 'max:20', 'alpha_dash', 'unique:categories,code'],
            'name_translations' => ['required', 'array'],
            'name_translations.*' => ['nullable', 'string', 'max:120'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $translations = $this->normalizeTranslations(
            (array) ($validated['name_translations'] ?? []),
            $availableLocales
        );

        if ($translations === []) {
            return back()
                ->withErrors(['name_translations' => __('messages.validation.translation_required')])
                ->withInput();
        }

        $category = Category::query()->create([
            'code' => Str::upper($validated['code']),
            'name' => $this->primaryName($translations),
            'name_translations' => $translations,
            'sort_order' => $validated['sort_order'] ?? 0,
            'is_active' => (bool) ($validated['is_active'] ?? true),
            'created_by' => $request->user()->id,
        ]);

        AuditLogger::event($request, $category, 'created', __('messages.audit.category_created'));

        return redirect()->route('admin.categories.index')->with('status', __('messages.status.category_created'));
    }

    public function update(Request $request, Category $category): RedirectResponse
    {
        $availableLocales = $this->availableLocales();

        $validated = $request->validate([
            'code' => ['required', 'string', 'max:20', 'alpha_dash', Rule::unique('categories', 'code')->ignore($category->id)],
            'name_translations' => ['required', 'array'],
            'name_translations.*' => ['nullable', 'string', 'max:120'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        $translations = $this->normalizeTranslations(
            (array) ($validated['name_translations'] ?? []),
            $availableLocales
        );

        if ($translations === []) {
            return back()
                ->withErrors(['name_translations' => __('messages.validation.translation_required')])
                ->withInput();
        }

        $category->update([
            'code' => Str::upper($validated['code']),
            'name' => $this->primaryName($translations),
            'name_translations' => $translations,
            'sort_order' => $validated['sort_order'] ?? $category->sort_order,
        ]);

        AuditLogger::event($request, $category, 'updated', __('messages.audit.category_updated'));

        return redirect()->route('admin.categories.index')->with('status', __('messages.status.category_updated'));
    }

    public function status(Request $request, Category $category): RedirectResponse
    {
        $category->update([
            'is_active' => ! $category->is_active,
        ]);

        AuditLogger::event($request, $category, 'status', __('messages.audit.category_status_changed'));

        return redirect()->route('admin.categories.index')->with('status', __('messages.status.category_status_changed'));
    }

    public function destroy(Request $request, Category $category): RedirectResponse
    {
        $category->delete();

        AuditLogger::event($request, $category, 'deleted', __('messages.audit.category_deleted'));

        return redirect()->route('admin.categories.index')->with('status', __('messages.status.category_deleted'));
    }

    /**
     * @return array<int, string>
     */
    private function availableLocales(): array
    {
        $locales = config('app.available_locales', ['az', 'en']);

        return $locales === [] ? ['az', 'en'] : array_values($locales);
    }

    /**
     * @param array<string, mixed> $rawTranslations
     * @param array<int, string> $availableLocales
     * @return array<string, string>
     */
    private function normalizeTranslations(array $rawTranslations, array $availableLocales): array
    {
        $translations = [];

        foreach ($availableLocales as $locale) {
            $value = trim((string) ($rawTranslations[$locale] ?? ''));

            if ($value !== '') {
                $translations[$locale] = $value;
            }
        }

        return $translations;
    }

    /**
     * @param array<string, string> $translations
     */
    private function primaryName(array $translations): string
    {
        $defaultLocale = config('app.locale', 'az');

        return $translations[$defaultLocale] ?? (string) reset($translations);
    }
}
