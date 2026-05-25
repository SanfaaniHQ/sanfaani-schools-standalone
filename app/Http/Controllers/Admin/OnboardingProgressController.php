<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OnboardingEventLog;
use App\Models\UserOnboardingProgress;

class OnboardingProgressController extends Controller
{
    public function index()
    {
        return view('admin.onboarding.progress', [
            'progress' => UserOnboardingProgress::query()
                ->with(['user', 'school', 'checklist', 'step'])
                ->latest()
                ->paginate(25),
            'events' => OnboardingEventLog::query()
                ->with(['user', 'school', 'demoSession'])
                ->latest()
                ->limit(20)
                ->get(),
        ]);
    }
}
