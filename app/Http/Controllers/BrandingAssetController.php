<?php

namespace App\Http\Controllers;

use App\Models\BrandingSetting;
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
}
