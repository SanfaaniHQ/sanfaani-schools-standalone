<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\School;
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
        ]);
    }
}
