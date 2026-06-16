<?php

namespace App\Http\Controllers;

use App\Services\CurrentSchoolService;
use App\Services\Portals\StudentPortalLinkService;
use Illuminate\View\View;

class ParentDashboardController extends Controller
{
    public function __invoke(
        CurrentSchoolService $currentSchool,
        StudentPortalLinkService $portalLinks
    ): View {
        $user = auth()->user();
        $school = $currentSchool->get($user);

        if (! $school) {
            return view('school.dashboard-not-assigned');
        }

        $linkedChildren = $portalLinks->childrenForParent($user, $school);
        $legacyChildren = $portalLinks->legacyChildrenForParent($user, $school);

        $children = $linkedChildren
            ->merge($legacyChildren)
            ->unique('id')
            ->values();

        $summary = [
            'total_children' => $children->count(),
            'active_children' => $children->where('status', 'active')->count(),
            'graduated_children' => $children->filter(fn ($student) => $student->isGraduated())->count(),
            'total_results' => $children->sum('results_count'),
            'total_attendance_records' => $children->sum('attendance_records_count'),
            'total_fee_invoices' => $children->sum('fee_invoices_count'),
            'total_report_cards' => $children->sum('report_card_snapshots_count'),
            'total_cbt_attempts' => $children->sum('cbt_attempts_count'),
        ];

        return view('parent.dashboard', [
            'school' => $school,
            'children' => $children,
            'summary' => $summary,
        ]);
    }
}