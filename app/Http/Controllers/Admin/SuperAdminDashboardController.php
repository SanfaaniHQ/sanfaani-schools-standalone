<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PaymentTransaction;
use App\Models\School;
use App\Models\ScratchCard;
use App\Models\ScratchCardBatch;
use App\Models\StudentResult;
use App\Models\User;
use Spatie\Permission\Models\Role;

class SuperAdminDashboardController extends Controller
{
    public function index()
    {
        return view('admin.dashboard', [
            'totalSchools' => School::count(),
            'totalUsers' => User::count(),
            'totalRoles' => Role::count(),
            'totalSuperAdmins' => User::role('super_admin')->count(),
            'totalSchoolAdmins' => User::role('school_admin')->count(),
            'totalResultOfficers' => User::role('result_officer')->count(),
            'activeSchools' => School::where('status', 'active')->count(),
            'trialSchools' => School::where('subscription_status', 'trial')->count(),
            'suspendedSchools' => School::where('status', 'suspended')->count(),
            'pendingScratchCardRequests' => ScratchCardBatch::where('status', 'pending_payment')->count(),
            'generatedScratchCardBatches' => ScratchCardBatch::where('status', 'generated')->count(),
            'pendingPayments' => PaymentTransaction::whereIn('status', ['pending', 'manual_pending'])->count(),
            'publishedResults' => StudentResult::where('status', 'published')->count(),
            'revokedScratchCards' => ScratchCard::where('status', 'revoked')->count(),
        ]);
    }
}
