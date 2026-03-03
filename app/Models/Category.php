<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Category extends Model
{
    /** @use HasFactory<\Database\Factories\CategoryFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'code',
        'name',
        'name_translations',
        'is_active',
        'sort_order',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'name_translations' => 'array',
        ];
    }

    public function getNameAttribute(?string $value): string
    {
        $translations = $this->attributes['name_translations'] ?? null;
        $translations = is_string($translations) ? json_decode($translations, true) : $translations;
        $translations = is_array($translations) ? $translations : [];

        $locale = app()->getLocale();
        $defaultLocale = config('app.locale', 'az');
        $fallbackLocale = config('app.fallback_locale');

        return $translations[$locale]
            ?? ($fallbackLocale ? ($translations[$fallbackLocale] ?? null) : null)
            ?? ($translations[$defaultLocale] ?? null)
            ?? (string) $value;
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }
}
