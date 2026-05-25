<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LicenseAuditLog;
use App\Models\School;
use App\Services\Licensing\LicenseActivationService;
use App\Services\Licensing\LicenseEntitlementService;
use App\Services\Licensing\LicenseValidationService;
use App\Services\System\DeploymentModeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use RuntimeException;

class LicenseController extends Controller
{
    public function __construct(
        private DeploymentModeService $deployment,
        private LicenseActivationService $activation,
        private LicenseValidationService $validation,
        private LicenseEntitlementService $entitlements,
    ) {}

    public function index(): View
    {
        $school = $this->defaultSchool();
        $result = $this->validation->validate($school);
        $license = $result->license ?? $this->validation->current($school);

        return view('admin.license.status', [
            'school' => $school,
            'license' => $license,
            'result' => $result,
            'deploymentMode' => $this->deployment->mode(),
            'licenseMode' => $this->deployment->licenseMode(),
            'daysUntilExpiry' => $this->validation->daysUntilExpiry($license),
            'shouldWarnExpiring' => $this->validation->shouldWarnExpiring($license),
            'entitlements' => $this->entitlements->entitlements($school),
            'features' => $this->entitlements->features($school),
            'auditLogs' => $this->auditLogs($license, $school),
        ]);
    }

    public function activate(): View
    {
        return view('admin.license.activate', [
            'schools' => School::orderBy('name')->get(),
            'defaultSchool' => $this->defaultSchool(),
            'licenseTypes' => config('licensing.types', []),
            'statusValues' => config('licensing.status_values', []),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'license_key' => ['required', 'string', 'max:255'],
            'license_type' => ['required', Rule::in(config('licensing.types', []))],
            'status' => ['nullable', Rule::in(config('licensing.status_values', []))],
            'school_id' => ['nullable', Rule::exists('schools', 'id')],
            'issued_to_name' => ['nullable', 'string', 'max:255'],
            'issued_to_email' => ['nullable', 'email', 'max:255'],
            'domain' => ['nullable', 'string', 'max:255'],
            'allowed_domains' => ['nullable', 'string', 'max:2000'],
            'features' => ['nullable', 'string', 'max:2000'],
            'entitlements' => ['nullable', 'string', 'max:2000'],
            'starts_at' => ['nullable', 'date'],
            'expires_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
        ]);

        $school = filled($data['school_id'] ?? null)
            ? School::find($data['school_id'])
            : $this->defaultSchool();

        try {
            $this->activation->activate([
                ...$data,
                'allowed_domains' => $this->parseList($data['allowed_domains'] ?? null),
                'features' => $this->parseMap($data['features'] ?? null),
                'entitlements' => $this->parseMap($data['entitlements'] ?? null),
            ], $school, $request);
        } catch (RuntimeException $exception) {
            return back()
                ->withInput($request->except('license_key'))
                ->with('error', $exception->getMessage());
        }

        return redirect()
            ->route('admin.license.index')
            ->with('success', 'License activated successfully.');
    }

    public function validateNow(): RedirectResponse
    {
        $result = $this->validation->validate($this->defaultSchool());

        return redirect()
            ->route('admin.license.index')
            ->with($result->valid() ? 'success' : 'error', $result->message);
    }

    private function defaultSchool(): ?School
    {
        if ($this->deployment->isSingleSchool() || $this->deployment->isManaged()) {
            return School::query()->orderBy('id')->first();
        }

        return null;
    }

    private function auditLogs($license, ?School $school)
    {
        return LicenseAuditLog::query()
            ->when($license, fn ($query) => $query->where('license_id', $license->id))
            ->when(! $license && $school, fn ($query) => $query->where('school_id', $school->id))
            ->latest()
            ->limit(15)
            ->get();
    }

    private function parseList(?string $value): array
    {
        return collect(preg_split('/[\r\n,]+/', (string) $value))
            ->map(fn (string $item): string => trim($item))
            ->filter()
            ->values()
            ->all();
    }

    private function parseMap(?string $value): array
    {
        return collect($this->parseList($value))
            ->mapWithKeys(fn (string $item): array => [$item => true])
            ->all();
    }
}
