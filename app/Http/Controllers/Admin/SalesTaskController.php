<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SalesTask;
use App\Services\Marketing\SalesTaskService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SalesTaskController extends Controller
{
    public function index(): View
    {
        return view('admin.sales.tasks', [
            'tasks' => SalesTask::query()
                ->with(['leadRequest:id,name,email,school_name,status', 'demoRequest:id,name,email,school_name', 'school:id,name', 'assignee:id,name,email'])
                ->latest()
                ->paginate(20),
        ]);
    }

    public function complete(SalesTask $salesTask, SalesTaskService $tasks): RedirectResponse
    {
        $tasks->complete($salesTask, request()->user());

        return back()->with('success', 'Sales task completed.');
    }
}
