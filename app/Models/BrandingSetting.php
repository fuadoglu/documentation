<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class BrandingSetting extends Model
{
    /** @use HasFactory<\Database\Factories\BrandingSettingFactory> */
    use HasFactory;

    public const CACHE_KEY = 'branding_settings.current';

    protected $fillable = [
        'company_name',
        'allowed_login_domain',
        'attachments_enabled',
        'primary_color',
        'secondary_color',
        'timezone',
        'logo_path',
        'favicon_path',
    ];

    protected function casts(): array
    {
        return [
            'attachments_enabled' => 'boolean',
        ];
    }

    public static function current(): self
    {
        return Cache::rememberForever(self::CACHE_KEY, function (): self {
            return self::query()->firstOrCreate(
                ['id' => 1],
                [
                    'company_name' => 'ECO DC',
                    'allowed_login_domain' => 'company.az',
                    'attachments_enabled' => true,
                    'timezone' => config('app.timezone', 'UTC'),
                ]
            );
        });
    }

    protected static function booted(): void
    {
        static::saved(fn () => Cache::forget(self::CACHE_KEY));
        static::deleted(fn () => Cache::forget(self::CACHE_KEY));
    }
}
