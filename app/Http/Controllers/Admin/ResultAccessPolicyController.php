<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicSession;
use App\Models\School;
use App\Models\SchoolResultAccessPolicy;
use App\Models\SchoolResultAccessPolicyRule;
use App\Models\Term;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ResultAccessPolicyController extends Controller
{
    public function index()
    {
        return view('admin.result-access-policies.index', [
            'policies' => SchoolResultAccessPolicy::with(['school', 'rules'])->latest()->paginate(15),
        ]);
    }

    public function create()
    {
        return view('admin.result-access-policies.form', $this->formData(new SchoolResultAccessPolicy([
            'access_mode' => 'scratch_card',
            'status' => 'active',
        ])));
    }

    public function store(Request $request)
    {
        $data = $this->validatePolicy($request);
        $ruleData = $this->validateRule($request, (int) $data['school_id']);

        $policy = SchoolResultAccessPolicy::create($data + ['created_by' => auth()->id()]);
        SchoolResultAccessPolicyRule::create($ruleData + ['school_result_access_policy_id' => $policy->id]);

        return redirect()
            ->route('admin.result-access-policies.index')
            ->with('success', 'Result access policy created.');
    }

    public function edit(SchoolResultAccessPolicy $resultAccessPolicy)
    {
        return view('admin.result-access-policies.form', $this->formData($resultAccessPolicy->load('rules')));
    }

    public function show(SchoolResultAccessPolicy $resultAccessPolicy)
    {
        return view('admin.result-access-policies.show', [
            'policy' => $resultAccessPolicy->load(['school', 'rules.academicSession', 'rules.term', 'createdBy']),
        ]);
    }

    public function update(Request $request, SchoolResultAccessPolicy $resultAccessPolicy)
    {
        $resultAccessPolicy->update($this->validatePolicy($request));

        $rule = $resultAccessPolicy->rules()->first();
        $ruleData = $this->validateRule($request, (int) $resultAccessPolicy->school_id);

        if ($rule) {
            $rule->update($ruleData);
        } else {
            SchoolResultAccessPolicyRule::create($ruleData + ['school_result_access_policy_id' => $resultAccessPolicy->id]);
        }

        return redirect()
            ->route('admin.result-access-policies.index')
            ->with('success', 'Result access policy updated.');
    }

    private function formData(SchoolResultAccessPolicy $policy): array
    {
        $schoolId = old('school_id', $policy->school_id);

        return [
            'policy' => $policy,
            'rule' => $policy->rules->first() ?? new SchoolResultAccessPolicyRule([
                'result_type' => 'term_result',
                'requires_scratch_card' => true,
                'status' => 'active',
            ]),
            'schools' => School::orderBy('name')->get(),
            'sessions' => $schoolId ? AcademicSession::where('school_id', $schoolId)->orderByDesc('id')->get() : collect(),
            'terms' => $schoolId ? Term::where('school_id', $schoolId)->orderByDesc('id')->get() : collect(),
        ];
    }

    private function validatePolicy(Request $request): array
    {
        return $request->validate([
            'school_id' => ['required', Rule::exists('schools', 'id')],
            'name' => ['required', 'string', 'max:255'],
            'access_mode' => ['required', Rule::in(['scratch_card', 'school_paid', 'parent_paid', 'hybrid'])],
            'status' => ['required', Rule::in(['active', 'inactive', 'archived'])],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'notes' => ['nullable', 'string', 'max:5000'],
        ]);
    }

    private function validateRule(Request $request, int $schoolId): array
    {
        $data = $request->validate([
            'academic_session_id' => ['nullable', Rule::exists('academic_sessions', 'id')->where('school_id', $schoolId)],
            'term_id' => ['nullable', Rule::exists('terms', 'id')->where('school_id', $schoolId)],
            'result_type' => ['required', Rule::in(['term_result'])],
            'access_scope' => ['required', Rule::in(['term', 'session', 'year', 'custom'])],
            'max_access_per_student' => ['nullable', 'integer', 'min:1'],
            'max_access_per_card' => ['nullable', 'integer', 'min:1'],
            'requires_scratch_card' => ['required', 'boolean'],
            'allows_parent_payment' => ['required', 'boolean'],
            'allows_school_paid_access' => ['required', 'boolean'],
            'allows_pdf_download' => ['required', 'boolean'],
            'rule_status' => ['required', Rule::in(['active', 'inactive'])],
        ], [], [
            'rule_status' => 'rule status',
        ]);

        $data['status'] = $data['rule_status'];
        unset($data['rule_status']);

        return $data;
    }
}
