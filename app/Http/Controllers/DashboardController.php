<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;

class DashboardController extends Controller
{
    public function __invoke(): RedirectResponse
    {
        if (auth()->user()->hasRole('super_admin')) {
            return redirect()->route('admin.dashboard');
        }

        if (auth()->user()->hasAnyRole(['school_admin', 'result_officer'])) {
            return redirect()->route('school.dashboard');
        }

        return redirect()->route('profile.edit');
    }
}
