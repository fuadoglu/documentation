<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class DocumentAttachment extends Model
{
    /** @use HasFactory<\Database\Factories\DocumentAttachmentFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'document_id',
        'version_number',
        'version_note',
        'original_name',
        'stored_name',
        'mime_type',
        'file_size',
        'sha256',
        'disk',
        'file_path',
        'uploaded_by',
    ];

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
