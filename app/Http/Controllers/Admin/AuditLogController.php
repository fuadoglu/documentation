<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Spatie\Activitylog\Models\Activity;

class AuditLogController extends Controller
{
    public function index(Request $request): View
    {
        $query = Activity::query()
            ->with(['causer:id,name,email'])
            ->latest('id');

        $this->applyFilters($query, $request);

        return view('admin.audit-logs.index', [
            'logs' => $query->paginate(25)->withQueryString(),
            'filters' => $request->only(['causer_id', 'event', 'date_from', 'date_to', 'module', 'subject_id', 'ip']),
            'users' => User::query()->where('is_active', true)->orderBy('name')->get(['id', 'name']),
            'modules' => ['Document', 'Category', 'Folder', 'User', 'BrandingSetting'],
            'events' => ['created', 'updated', 'deleted', 'status', 'downloaded', 'reset_password'],
        ]);
    }

    public function export(Request $request): StreamedResponse
    {
        $query = Activity::query()
            ->with(['causer:id,name,email'])
            ->latest('id');

        $this->applyFilters($query, $request);

        $filenamePrefix = Str::of(__('ui.admin.audit.csv_filename_prefix'))->slug('-')->toString();
        if ($filenamePrefix === '') {
            $filenamePrefix = 'audit-logs';
        }
        $filename = $filenamePrefix.'-'.now()->format('Ymd_His').'.csv';

        return response()->streamDownload(function () use ($query): void {
            $handle = fopen('php://output', 'w');

            if (! $handle) {
                return;
            }

            fputcsv($handle, [
                __('ui.admin.audit.csv_headers.id'),
                __('ui.admin.audit.csv_headers.created_at'),
                __('ui.admin.audit.csv_headers.causer_name'),
                __('ui.admin.audit.csv_headers.causer_email'),
                __('ui.admin.audit.csv_headers.event'),
                __('ui.admin.audit.csv_headers.subject_type'),
                __('ui.admin.audit.csv_headers.subject_id'),
                __('ui.admin.audit.csv_headers.description'),
                __('ui.admin.audit.csv_headers.ip_address'),
                __('ui.admin.audit.csv_headers.path'),
                __('ui.admin.audit.csv_headers.method'),
            ]);

            $query->chunk(500, function ($rows) use ($handle): void {
                foreach ($rows as $log) {
                    $subjectType = class_basename($log->subject_type ?? '');
                    $subjectTypeKey = 'ui.admin.audit.module_values.'.$subjectType;
                    $subjectTypeLabel = __($subjectTypeKey);

                    $eventName = (string) ($log->event ?? '');
                    $eventNameKey = 'ui.admin.audit.event_values.'.$eventName;
                    $eventNameLabel = __($eventNameKey);

                    fputcsv($handle, [
                        $log->id,
                        optional($log->created_at)->toDateTimeString(),
                        $log->causer?->name,
                        $log->causer?->email,
                        $eventNameLabel === $eventNameKey ? $eventName : $eventNameLabel,
                        $subjectTypeLabel === $subjectTypeKey ? $subjectType : $subjectTypeLabel,
                        $log->subject_id,
                        $log->description,
                        data_get($log->properties, 'ip_address'),
                        data_get($log->properties, 'path'),
                        data_get($log->properties, 'method'),
                    ]);
                }
            });

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    private function applyFilters($query, Request $request): void
    {
        if ($request->filled('causer_id')) {
            $query->where('causer_id', $request->integer('causer_id'));
        }

        if ($request->filled('event')) {
            $query->where('event', $request->string('event')->toString());
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->string('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->string('date_to'));
        }

        if ($request->filled('module')) {
            $module = $request->string('module')->toString();
            $query->where('subject_type', 'like', '%\\'.$module);
        }

        if ($request->filled('subject_id')) {
            $query->where('subject_id', $request->integer('subject_id'));
        }

        if ($request->filled('ip')) {
            $ip = $request->string('ip')->toString();
            $query->where('properties', 'like', '%"ip_address":"'.$ip.'"%');
        }
    }
}
