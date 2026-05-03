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
            ->when($request->filled('action'), fn ($query) => $query->where('action', 'like', '%' . $request->input('action') . '%'))
            ->latest()
            ->paginate(25)
            ->withQueryString();

        return view('admin.audit-logs.index', [
            'logs' => $logs,
            'schools' => School::withTrashed()->orderBy('name')->get(),
            'users' => User::orderBy('name')->get(),
            'filters' => $request->only(['school_id', 'user_id', 'action']),
        ]);
    }
}
