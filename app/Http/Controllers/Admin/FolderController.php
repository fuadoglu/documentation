<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Folder;
use App\Support\AuditLogger;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class FolderController extends Controller
{
    public function index(): View
    {
        $locales = $this->availableLocales();

        return view('admin.folders.index', [
            'folders' => Folder::query()
                ->with('parent:id,name,name_translations')
                ->orderBy('sort_order')
                ->orderBy('name')
                ->paginate(20),
            'parentFolders' => Folder::query()
                ->whereNull('parent_id')
                ->orderBy('name')
                ->get(['id', 'name', 'name_translations']),
            'locales' => $locales,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $availableLocales = $this->availableLocales();

        $validated = $request->validate([
            'code' => ['required', 'string', 'max:30', 'alpha_dash', 'unique:folders,code'],
            'name_translations' => ['required', 'array'],
            'name_translations.*' => ['nullable', 'string', 'max:150'],
            'parent_id' => [
                'nullable',
                'integer',
                Rule::exists('folders', 'id')->where(fn ($query) => $query->whereNull('parent_id')),
            ],
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

        $folder = Folder::query()->create([
            'code' => Str::upper($validated['code']),
            'name' => $this->primaryName($translations),
            'name_translations' => $translations,
            'parent_id' => $validated['parent_id'] ?? null,
            'sort_order' => $validated['sort_order'] ?? 0,
            'is_active' => (bool) ($validated['is_active'] ?? true),
            'created_by' => $request->user()->id,
        ]);

        AuditLogger::event($request, $folder, 'created', __('messages.audit.folder_created'));

        return redirect()->route('admin.folders.index')->with('status', __('messages.status.folder_created'));
    }

    public function update(Request $request, Folder $folder): RedirectResponse
    {
        $availableLocales = $this->availableLocales();

        $validated = $request->validate([
            'code' => ['required', 'string', 'max:30', 'alpha_dash', Rule::unique('folders', 'code')->ignore($folder->id)],
            'name_translations' => ['required', 'array'],
            'name_translations.*' => ['nullable', 'string', 'max:150'],
            'parent_id' => [
                'nullable',
                'integer',
                Rule::exists('folders', 'id')->where(fn ($query) => $query->whereNull('parent_id')),
                Rule::notIn([$folder->id]),
            ],
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

        $folder->update([
            'code' => Str::upper($validated['code']),
            'name' => $this->primaryName($translations),
            'name_translations' => $translations,
            'parent_id' => $validated['parent_id'] ?? null,
            'sort_order' => $validated['sort_order'] ?? $folder->sort_order,
        ]);

        AuditLogger::event($request, $folder, 'updated', __('messages.audit.folder_updated'));

        return redirect()->route('admin.folders.index')->with('status', __('messages.status.folder_updated'));
    }

    public function status(Request $request, Folder $folder): RedirectResponse
    {
        $folder->update([
            'is_active' => ! $folder->is_active,
        ]);

        AuditLogger::event($request, $folder, 'status', __('messages.audit.folder_status_changed'));

        return redirect()->route('admin.folders.index')->with('status', __('messages.status.folder_status_changed'));
    }

    public function destroy(Request $request, Folder $folder): RedirectResponse
    {
        $folder->delete();

        AuditLogger::event($request, $folder, 'deleted', __('messages.audit.folder_deleted'));

        return redirect()->route('admin.folders.index')->with('status', __('messages.status.folder_deleted'));
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
