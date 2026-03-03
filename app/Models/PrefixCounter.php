<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PrefixCounter extends Model
{
    /** @use HasFactory<\Database\Factories\PrefixCounterFactory> */
    use HasFactory;

    protected $fillable = [
        'folder_id',
        'category_id',
        'year',
        'last_number',
    ];

    public function folder(): BelongsTo
    {
        return $this->belongsTo(Folder::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
}
