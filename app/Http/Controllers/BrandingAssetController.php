<?php

namespace App\Http\Controllers;

use App\Models\BrandingSetting;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BrandingAssetController extends Controller
{
    public function logo(): StreamedResponse
    {
        $path = BrandingSetting::current()->logo_path;

        abort_if(blank($path), 404, __('messages.error.file_not_found'));
        abort_unless(Storage::disk('public')->exists($path), 404, __('messages.error.file_not_found'));

        return Storage::disk('public')->response($path);
    }

    public function favicon(): StreamedResponse
    {
        $path = BrandingSetting::current()->favicon_path;

        abort_if(blank($path), 404, __('messages.error.file_not_found'));
        abort_unless(Storage::disk('public')->exists($path), 404, __('messages.error.file_not_found'));

        return Storage::disk('public')->response($path);
    }

    public function theme(): Response
    {
        $settings = BrandingSetting::current();

        $primary = $this->normalizeHex((string) ($settings->primary_color ?: '#0F766E'), '0f766e');
        $secondary = $this->normalizeHex((string) ($settings->secondary_color ?: '#0B132B'), '0b132b');

        $primaryRgb = $this->hexToRgb($primary);
        $secondaryRgb = $this->hexToRgb($secondary);

        $css = implode("\n", [
            ':root {',
            "  --color-brand: {$primary};",
            "  --color-brand-rgb: {$primaryRgb};",
            "  --color-brand-dark: {$secondary};",
            "  --color-brand-dark-rgb: {$secondaryRgb};",
            "  --color-brand-soft: rgba({$primaryRgb}, 0.12);",
            "  --color-brand-soft-border: rgba({$primaryRgb}, 0.32);",
            "  --color-brand-soft-text: rgba({$primaryRgb}, 0.92);",
            "  --color-brand-ring: rgba({$primaryRgb}, 0.25);",
            '}',
            '',
        ]);

        return response($css, 200, [
            'Content-Type' => 'text/css; charset=UTF-8',
            'Cache-Control' => 'public, max-age=300',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    private function normalizeHex(string $hex, string $fallback): string
    {
        $normalized = ltrim(trim($hex), '#');

        if (strlen($normalized) === 3) {
            $normalized = preg_replace('/(.)/', '$1$1', $normalized) ?? $fallback;
        }

        if (strlen($normalized) !== 6 || ! ctype_xdigit($normalized)) {
            $normalized = $fallback;
        }

        return '#'.strtolower($normalized);
    }

    private function hexToRgb(string $hex): string
    {
        $normalized = ltrim($hex, '#');
        $r = hexdec(substr($normalized, 0, 2));
        $g = hexdec(substr($normalized, 2, 2));
        $b = hexdec(substr($normalized, 4, 2));

        return "{$r}, {$g}, {$b}";
    }
}
