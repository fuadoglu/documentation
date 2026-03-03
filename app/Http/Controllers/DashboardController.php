<?php

namespace App\Http\Controllers;

use App\Models\BrandingSetting;
use App\Models\Document;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request): View
    {
        $user = $request->user();
        $canViewDocuments = $user->can('documents.view');
        $canCreateDocuments = $user->can('documents.create');

        $documentQuery = Document::query();

        if (! $user->hasRole('admin')) {
            $documentQuery->where('created_by', $user->id);
        }

        $recentDocuments = null;
        $totalDocuments = 0;
        $todayDocuments = 0;

        if ($canViewDocuments) {
            $recentDocuments = (clone $documentQuery)
                ->with(['category:id,name,name_translations,code', 'folder:id,name,name_translations,parent_id', 'creator:id,name'])
                ->latest()
                ->paginate(8, ['*'], 'dashboard_page')
                ->withQueryString();

            $totalDocuments = (clone $documentQuery)->count();
            $todayDocuments = (clone $documentQuery)->whereDate('created_at', now()->toDateString())->count();
        }

        return view('dashboard', [
            'totalDocuments' => $totalDocuments,
            'todayDocuments' => $todayDocuments,
            'recentDocuments' => $recentDocuments,
            'attachmentsEnabled' => BrandingSetting::current()->attachments_enabled,
            'canViewDocuments' => $canViewDocuments,
            'canCreateDocuments' => $canCreateDocuments,
        ]);
    }
}
