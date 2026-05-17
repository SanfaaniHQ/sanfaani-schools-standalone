<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\School;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        $logs = $this->filteredQuery($request)
            ->with(['user', 'school'])
            ->latest()
            ->paginate(25)
            ->withQueryString();

        return view('admin.audit-logs.index', [
            'logs' => $logs,
            'schools' => School::withTrashed()->orderBy('name')->get(),
            'users' => User::orderBy('name')->get(),
            'tags' => AuditLog::query()->whereNotNull('action_tag')->distinct()->orderBy('action_tag')->pluck('action_tag'),
            'categories' => AuditLog::query()->whereNotNull('category')->distinct()->orderBy('category')->pluck('category'),
            'filters' => $request->only(['school_id', 'user_id', 'action', 'action_tag', 'category', 'severity', 'auditable_type', 'date_from', 'date_to']),
        ]);
    }

    public function export(Request $request): StreamedResponse
    {
        $fileName = 'audit-logs-'.now()->format('Ymd-His').'.csv';

        return response()->streamDownload(function () use ($request) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, [
                'ID',
                'Action',
                'Category',
                'Severity',
                'Actor',
                'School',
                'Auditable Type',
                'Auditable ID',
                'IP Address',
                'Metadata',
                'Old Values',
                'New Values',
                'Created At',
            ]);

            $this->filteredQuery($request)
                ->with(['user:id,name', 'school:id,name'])
                ->orderBy('id')
                ->chunkById(500, function ($logs) use ($handle) {
                    foreach ($logs as $log) {
                        fputcsv($handle, $this->csvRow($log));
                    }
                });

            fclose($handle);
        }, $fileName, ['Content-Type' => 'text/csv']);
    }

    private function filteredQuery(Request $request): Builder
    {
        return AuditLog::query()
            ->when($request->filled('school_id'), fn ($query) => $query->where('school_id', $request->input('school_id')))
            ->when($request->filled('user_id'), fn ($query) => $query->where('user_id', $request->input('user_id')))
            ->when($request->filled('action'), function ($query) use ($request) {
                $search = $request->input('action');

                $query->where(function ($query) use ($search) {
                    $query->where('action', 'like', '%'.$search.'%')
                        ->orWhere('action_tag', 'like', '%'.$search.'%')
                        ->orWhere('event', 'like', '%'.$search.'%')
                        ->orWhere('category', 'like', '%'.$search.'%');
                });
            })
            ->when($request->filled('category'), fn ($query) => $query->where('category', $request->input('category')))
            ->when($request->filled('action_tag'), fn ($query) => $query->where('action_tag', $request->input('action_tag')))
            ->when($request->filled('severity'), fn ($query) => $query->where('severity', $request->input('severity')))
            ->when($request->filled('auditable_type'), fn ($query) => $query->where('auditable_type', 'like', '%'.$request->input('auditable_type').'%'))
            ->when($request->filled('date_from'), fn ($query) => $query->whereDate('created_at', '>=', $request->input('date_from')))
            ->when($request->filled('date_to'), fn ($query) => $query->whereDate('created_at', '<=', $request->input('date_to')));
    }

    private function csvRow(AuditLog $log): array
    {
        return [
            $log->id,
            $log->event ?? $log->action,
            $log->category ?? $log->action_tag,
            $log->severity,
            $log->user?->name ?? $log->actor_type ?? 'System',
            $log->school?->name ?? 'Platform',
            $log->auditable_type ? class_basename($log->auditable_type) : null,
            $log->auditable_id,
            $log->ip_address,
            $this->encodeForCsv($log->payload ?? $log->metadata),
            $this->encodeForCsv($log->old_values),
            $this->encodeForCsv($log->new_values),
            $log->created_at?->toDateTimeString(),
        ];
    }

    private function encodeForCsv(mixed $value): ?string
    {
        if ($value === null || $value === []) {
            return null;
        }

        return json_encode($value, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }
}
