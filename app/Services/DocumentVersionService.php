<?php

namespace App\Services;

use App\Models\Document;
use App\Models\DocumentAttachment;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

class DocumentVersionService
{
    public function create(Document $document, UploadedFile $file, int $uploadedBy, ?string $versionNote = null): DocumentAttachment
    {
        $storedName = Str::uuid().'.'.$file->extension();
        $dir = 'documents/'.now()->format('Y/m');
        $disk = Storage::disk('local');
        $path = $disk->putFileAs($dir, $file, $storedName);

        if (! is_string($path) || $path === '') {
            throw new RuntimeException(__('messages.error.file_store_failed'));
        }

        try {
            return DB::transaction(function () use ($document, $file, $uploadedBy, $versionNote, $storedName, $path): DocumentAttachment {
                Document::query()->whereKey($document->id)->lockForUpdate()->firstOrFail();

                $nextVersionNumber = ((int) DocumentAttachment::withTrashed()
                    ->where('document_id', $document->id)
                    ->max('version_number')) + 1;

                return DocumentAttachment::query()->create([
                    'document_id' => $document->id,
                    'version_number' => $nextVersionNumber,
                    'version_note' => $versionNote,
                    'original_name' => $file->getClientOriginalName(),
                    'stored_name' => $storedName,
                    'mime_type' => (string) $file->getClientMimeType(),
                    'file_size' => $file->getSize(),
                    'sha256' => hash_file('sha256', $file->getRealPath()) ?: null,
                    'disk' => 'local',
                    'file_path' => $path,
                    'uploaded_by' => $uploadedBy,
                ]);
            }, 3);
        } catch (\Throwable $exception) {
            if ($disk->exists($path)) {
                $disk->delete($path);
            }

            throw $exception;
        }
    }
}
