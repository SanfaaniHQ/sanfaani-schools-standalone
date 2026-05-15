<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\School;
use App\Models\User;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        $logs = AuditLog::with(['user', 'school'])
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
            ->when($request->filled('date_to'), fn ($query) => $query->whereDate('created_at', '<=', $request->input('date_to')))
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
}
