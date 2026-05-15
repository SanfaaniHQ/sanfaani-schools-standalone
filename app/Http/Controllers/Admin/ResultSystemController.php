<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SchoolResultAccessPolicy;
use App\Models\ScratchCardBatch;
use App\Models\StudentResult;
use App\Models\SubscriptionPlan;

class ResultSystemController extends Controller
{
    public function index()
    {
        return view('admin.result-system.index', [
            'publishedResults' => StudentResult::where('status', 'published')->count(),
            'policies' => SchoolResultAccessPolicy::count(),
            'activePlans' => SubscriptionPlan::where('status', 'active')->count(),
            'pendingScratchRequests' => ScratchCardBatch::where('status', 'pending_payment')->count(),
        ]);
    }
}
