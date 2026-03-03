<?php

namespace App\Http\Controllers;

use App\Models\BrandingSetting;
use App\Models\Category;
use App\Models\Document;
use App\Models\Folder;
use App\Models\User;
use App\Services\DocumentVersionService;
use App\Services\PrefixGenerator;
use App\Support\AuditLogger;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DocumentController extends Controller
{
    public function __construct(
        private readonly PrefixGenerator $prefixGenerator,
        private readonly DocumentVersionService $documentVersionService,
    )
    {
    }

    public function index(Request $request): View
    {
        $user = $request->user();
        $isAdmin = $user->hasRole('admin');

        $query = Document::query()
            ->with(['category:id,name,name_translations,code', 'folder:id,name,name_translations,parent_id', 'creator:id,name'])
            ->latest();

        if (! $isAdmin) {
            $query->where('created_by', $user->id);
        }

        if ($request->filled('q')) {
            $query->where('title', 'like', '%'.$request->string('q')->trim().'%');
        }

        if ($isAdmin && $request->filled('created_by')) {
            $query->where('created_by', $request->integer('created_by'));
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->integer('category_id'));
        }

        if ($request->filled('folder_id')) {
            $query->where('folder_id', $request->integer('folder_id'));
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->string('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->string('date_to'));
        }

        return view('documents.index', [
            'documents' => $query->paginate(15)->withQueryString(),
            'filters' => $request->only(['q', 'created_by', 'category_id', 'folder_id', 'date_from', 'date_to']),
            'users' => $isAdmin
                ? User::query()->where('is_active', true)->orderBy('name')->get(['id', 'name'])
                : User::query()->whereKey($user->id)->get(['id', 'name']),
            'canFilterUsers' => $isAdmin,
            'categories' => Category::query()->where('is_active', true)->orderBy('sort_order')->orderBy('name')->get(['id', 'name', 'name_translations', 'code']),
            'folders' => Folder::query()->with('parent:id,name,name_translations')->where('is_active', true)->orderBy('sort_order')->orderBy('name')->get(['id', 'name', 'name_translations', 'parent_id']),
        ]);
    }

    public function create(): View
    {
        return view('documents.create', [
            'categories' => Category::query()->where('is_active', true)->orderBy('sort_order')->orderBy('name')->get(['id', 'name', 'name_translations', 'code']),
            'folders' => Folder::query()
                ->with('parent:id,name,name_translations')
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get(['id', 'name', 'name_translations', 'parent_id']),
            'attachmentsEnabled' => BrandingSetting::current()->attachments_enabled,
        ]);
    }

    public function prefixPreview(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'category_id' => [
                'required',
                'integer',
                Rule::exists('categories', 'id')->where(fn ($query) => $query->where('is_active', true)),
            ],
            'folder_id' => [
                'required',
                'integer',
                Rule::exists('folders', 'id')->where(fn ($query) => $query->where('is_active', true)),
            ],
        ]);

        $category = Category::query()->findOrFail($validated['category_id']);
        $folder = Folder::query()->findOrFail($validated['folder_id']);

        return response()->json([
            'prefix_code' => $this->prefixGenerator->preview($category, $folder),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $attachmentsEnabled = BrandingSetting::current()->attachments_enabled;

        $rules = [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'category_id' => [
                'required',
                'integer',
                Rule::exists('categories', 'id')->where(fn ($query) => $query->where('is_active', true)),
            ],
            'folder_id' => [
                'required',
                'integer',
                Rule::exists('folders', 'id')->where(fn ($query) => $query->where('is_active', true)),
            ],
        ];

        if ($attachmentsEnabled) {
            $rules['file'] = ['nullable', 'file', 'mimes:pdf,doc,docx,xls,xlsx,png,jpg,jpeg', 'max:10240'];
        } else {
            $rules['file'] = ['prohibited'];
        }

        $validated = $request->validate($rules, [
            'file.prohibited' => __('messages.validation.file_upload_disabled'),
        ]);

        $category = Category::query()->findOrFail($validated['category_id']);
        $folder = Folder::query()->findOrFail($validated['folder_id']);

        $document = Document::query()->create([
            'prefix_code' => $this->prefixGenerator->generate($category, $folder),
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'category_id' => $validated['category_id'],
            'folder_id' => $validated['folder_id'],
            'created_by' => $request->user()->id,
        ]);

        if ($attachmentsEnabled && $request->hasFile('file')) {
            $this->documentVersionService->create(
                $document,
                $request->file('file'),
                $request->user()->id,
                __('ui.documents.initial_version_note')
            );
        }

        AuditLogger::event($request, $document, 'created', __('messages.audit.document_created'));

        return redirect()
            ->route('documents.show', $document)
            ->with('status', __('messages.status.document_created'));
    }

    public function edit(Request $request, Document $document): View
    {
        abort_unless($this->canManageDocument($request->user(), $document), 403);

        $attachmentsEnabled = BrandingSetting::current()->attachments_enabled;

        if ($attachmentsEnabled) {
            $document->load('attachments.uploader:id,name');
        }

        return view('documents.edit', [
            'document' => $document,
            'categories' => Category::query()->where('is_active', true)->orderBy('sort_order')->orderBy('name')->get(['id', 'name', 'name_translations', 'code']),
            'folders' => Folder::query()
                ->with('parent:id,name,name_translations')
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get(['id', 'name', 'name_translations', 'parent_id']),
            'attachmentsEnabled' => $attachmentsEnabled,
            'canManage' => true,
        ]);
    }

    public function update(Request $request, Document $document): RedirectResponse
    {
        abort_unless($this->canManageDocument($request->user(), $document), 403);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'category_id' => [
                'required',
                'integer',
                Rule::exists('categories', 'id')->where(fn ($query) => $query->where('is_active', true)),
            ],
            'folder_id' => [
                'required',
                'integer',
                Rule::exists('folders', 'id')->where(fn ($query) => $query->where('is_active', true)),
            ],
        ]);

        $category = Category::query()->findOrFail($validated['category_id']);
        $folder = Folder::query()->findOrFail($validated['folder_id']);

        $payload = [
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'category_id' => $validated['category_id'],
            'folder_id' => $validated['folder_id'],
        ];

        if (
            (int) $document->category_id !== (int) $validated['category_id']
            || (int) $document->folder_id !== (int) $validated['folder_id']
        ) {
            $payload['prefix_code'] = $this->prefixGenerator->generate($category, $folder);
        }

        $document->update($payload);

        AuditLogger::event($request, $document, 'updated', __('messages.audit.document_updated'));

        return redirect()
            ->route('documents.show', $document)
            ->with('status', __('messages.status.document_updated'));
    }

    public function destroy(Request $request, Document $document): RedirectResponse
    {
        abort_unless($this->canManageDocument($request->user(), $document), 403);

        $document->delete();

        AuditLogger::event($request, $document, 'deleted', __('messages.audit.document_deleted'));

        return redirect()
            ->route('documents.index')
            ->with('status', __('messages.status.document_deleted'));
    }

    public function show(Request $request, Document $document): View
    {
        abort_unless($this->canAccessDocument($request->user(), $document), 403);

        $attachmentsEnabled = BrandingSetting::current()->attachments_enabled;

        $relations = [
            'category:id,name,name_translations,code',
            'folder:id,name,name_translations,parent_id',
            'folder.parent:id,name,name_translations',
            'creator:id,name,email',
        ];

        if ($attachmentsEnabled) {
            $relations[] = 'attachments.uploader:id,name';
        }

        $document->load($relations);

        return view('documents.show', [
            'document' => $document,
            'attachmentsEnabled' => $attachmentsEnabled,
            'canManage' => $this->canManageDocument($request->user(), $document),
        ]);
    }

    private function canAccessDocument(User $user, Document $document): bool
    {
        return $user->hasRole('admin') || $document->created_by === $user->id;
    }

    private function canManageDocument(User $user, Document $document): bool
    {
        return $this->canAccessDocument($user, $document);
    }
}
