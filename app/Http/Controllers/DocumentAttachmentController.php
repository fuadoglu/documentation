<?php

namespace App\Http\Controllers;

use App\Models\BrandingSetting;
use App\Models\Document;
use App\Models\DocumentAttachment;
use App\Services\DocumentVersionService;
use App\Support\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DocumentAttachmentController extends Controller
{
    public function __construct(private readonly DocumentVersionService $documentVersionService)
    {
    }

    public function storeVersion(Request $request, Document $document)
    {
        abort_unless(BrandingSetting::current()->attachments_enabled, 404);
        abort_unless($this->canAccessDocument($request, $document), 403);

        $validated = $request->validate([
            'file' => ['required', 'file', 'mimes:pdf,doc,docx,xls,xlsx,png,jpg,jpeg', 'max:10240'],
            'version_note' => ['required', 'string', 'max:500'],
        ]);

        $this->documentVersionService->create(
            $document,
            $request->file('file'),
            $request->user()->id,
            $validated['version_note'],
        );

        AuditLogger::event($request, $document, 'updated', __('messages.audit.document_version_uploaded'));

        return redirect()
            ->route('documents.show', $document)
            ->with('status', __('messages.status.document_version_uploaded'));
    }

    public function download(Request $request, Document $document, DocumentAttachment $attachment): StreamedResponse
    {
        abort_unless(BrandingSetting::current()->attachments_enabled, 404);
        abort_unless($attachment->document_id === $document->id, 404);
        abort_unless($this->canAccessDocument($request, $document), 403);

        $disk = Storage::disk($attachment->disk);

        if (! $disk->exists($attachment->file_path)) {
            abort(404, __('messages.error.file_not_found'));
        }

        if ($attachment->sha256) {
            try {
                $checksum = $disk->checksum($attachment->file_path);
            } catch (\Throwable) {
                abort(403, __('messages.error.file_integrity_failed'));
            }

            if (! is_string($checksum) || ! hash_equals(strtolower($attachment->sha256), strtolower($checksum))) {
                abort(403, __('messages.error.file_integrity_failed'));
            }
        }

        AuditLogger::event($request, $document, 'downloaded', __('messages.audit.document_attachment_downloaded'));

        return $disk->download($attachment->file_path, $attachment->original_name);
    }

    public function destroyVersion(Request $request, Document $document, DocumentAttachment $attachment): RedirectResponse
    {
        abort_unless(BrandingSetting::current()->attachments_enabled, 404);
        abort_unless($attachment->document_id === $document->id, 404);
        abort_unless($this->canAccessDocument($request, $document), 403);

        $disk = Storage::disk($attachment->disk);

        if ($attachment->file_path && $disk->exists($attachment->file_path)) {
            $disk->delete($attachment->file_path);
        }

        $attachment->delete();

        AuditLogger::event($request, $document, 'updated', __('messages.audit.document_version_deleted'));

        return redirect()
            ->route('documents.show', $document)
            ->with('status', __('messages.status.document_version_deleted'));
    }

    private function canAccessDocument(Request $request, Document $document): bool
    {
        $user = $request->user();

        return $user && ($user->hasRole('admin') || $document->created_by === $user->id);
    }
}
