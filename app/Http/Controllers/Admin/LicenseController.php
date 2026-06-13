<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\License;
use App\Models\LicenseAuditLog;
use App\Models\School;
use App\Services\AuditLogService;
use App\Services\Licensing\LicenseActivationService;
use App\Services\Licensing\LicenseDiagnosticsService;
use App\Services\Licensing\LicenseEntitlementService;
use App\Services\Licensing\LicenseValidationService;
use App\Services\System\DeploymentModeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
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
        private LicenseDiagnosticsService $diagnostics,
        private AuditLogService $auditLog,
    ) {}

    public function index(): View
    {
        $school = $this->defaultSchool();
        $result = $this->validation->validate($school);
        $license = $result->license ?? $this->validation->current($school);
        $entitlements = $this->entitlements->entitlements($school);
        $features = $this->entitlements->features($school);

        $this->auditMain('license_status_viewed', $license, $school, [
            'license_status' => $result->status,
            'enabled_entitlement_count' => $this->enabledCount($entitlements),
            'enabled_feature_count' => $this->enabledCount($features),
        ]);
        $this->auditMain('license_entitlements_viewed', $license, $school, [
            'license_status' => $result->status,
            'enabled_entitlement_count' => $this->enabledCount($entitlements),
            'enabled_feature_count' => $this->enabledCount($features),
        ]);

        return view('admin.license.status', [
            'school' => $school,
            'license' => $license,
            'result' => $result,
            'deploymentMode' => $this->deployment->mode(),
            'licenseMode' => $this->deployment->licenseMode(),
            'daysUntilExpiry' => $this->validation->daysUntilExpiry($license),
            'shouldWarnExpiring' => $this->validation->shouldWarnExpiring($license),
            'entitlements' => $entitlements,
            'features' => $features,
            'supportDiagnostics' => $this->diagnostics->supportSummary($school, $result, $license),
            'entitlementRows' => $this->diagnostics->entitlementRows($school, $license),
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
        $requestedSchool = $this->schoolFromRequest($request);
        $this->auditMain('license_activation_attempted', null, $requestedSchool, $this->activationMetadata($request->all(), $requestedSchool));

        $validator = Validator::make($request->all(), [
            'license_key' => ['required', 'string', 'min:12', 'max:255', 'regex:/\A[A-Za-z0-9][A-Za-z0-9\-]*[A-Za-z0-9]\z/'],
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
        ], [
            'license_key.regex' => 'Use only letters, numbers, and hyphens. Do not paste spaces or secret notes into the license key.',
        ]);

        if ($validator->fails()) {
            $this->auditMain('license_activation_failed', null, $requestedSchool, $this->activationMetadata($request->all(), $requestedSchool, 'validation_failed'));

            return back()
                ->withErrors($validator)
                ->withInput($request->except('license_key'));
        }

        $data = $validator->validated();
        $school = filled($data['school_id'] ?? null)
            ? School::find($data['school_id'])
            : $this->defaultSchool();

        try {
            $license = $this->activation->activate([
                ...$data,
                'allowed_domains' => $this->parseList($data['allowed_domains'] ?? null),
                'features' => $this->parseMap($data['features'] ?? null),
                'entitlements' => $this->parseMap($data['entitlements'] ?? null),
            ], $school, $request);
        } catch (RuntimeException $exception) {
            $this->auditMain('license_activation_failed', null, $school, $this->activationMetadata($data, $school, 'activation_runtime_error'));

            return back()
                ->withInput($request->except('license_key'))
                ->with('error', $exception->getMessage());
        }

        $this->auditMain('license_activation_succeeded', $license, $school, [
            'license_status' => $license->status,
            'enabled_entitlement_count' => $this->enabledCount((array) $license->entitlements),
            'enabled_feature_count' => $this->enabledCount((array) $license->features),
        ]);

        return redirect()
            ->route('admin.license.index')
            ->with('success', 'License activated successfully.');
    }

    public function validateNow(Request $request): RedirectResponse
    {
        $result = $this->validation->validate($this->defaultSchool());
        $license = $result->license ?? $this->validation->current($this->defaultSchool());

        $this->auditMain('license_validation_check_ran', $license, $this->defaultSchool(), [
            'license_status' => $result->status,
            'failed_reason_code' => $result->valid() ? null : $result->status,
        ], $request);

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
            ->mapWithKeys(function (string $item): array {
                [$key, $enabled] = array_pad(preg_split('/[:=]/', $item, 2), 2, 'true');
                $enabled = str($enabled)->trim()->lower()->toString();

                return [trim($key) => ! in_array($enabled, ['0', 'false', 'disabled', 'off', 'no'], true)];
            })
            ->all();
    }

    private function schoolFromRequest(Request $request): ?School
    {
        $schoolId = $request->input('school_id');

        return filled($schoolId) && is_numeric($schoolId)
            ? School::find((int) $schoolId)
            : $this->defaultSchool();
    }

    private function activationMetadata(array $data, ?School $school, ?string $failedReasonCode = null): array
    {
        return [
            'actor_id' => auth()->id(),
            'school_id' => $school?->id,
            'deployment_mode' => $this->deployment->mode(),
            'license_status' => $data['status'] ?? 'active',
            'edition' => $data['license_type'] ?? $this->deployment->licenseMode(),
            'enabled_entitlement_count' => $this->enabledCount($this->parseMap((string) ($data['entitlements'] ?? ''))),
            'enabled_feature_count' => $this->enabledCount($this->parseMap((string) ($data['features'] ?? ''))),
            'domain_present' => filled($data['domain'] ?? null),
            'failed_reason_code' => $failedReasonCode,
        ];
    }

    private function auditMain(string $action, ?License $license, ?School $school, array $metadata = [], ?Request $request = null): void
    {
        $this->auditLog->log($action, $license, $school, metadata: array_filter([
            'actor_id' => auth()->id(),
            'school_id' => $school?->id,
            'deployment_mode' => $this->deployment->mode(),
            'license_status' => $metadata['license_status'] ?? $license?->status,
            'license_id' => $license?->id,
            'edition' => $metadata['edition'] ?? $license?->license_type,
            'enabled_entitlement_count' => $metadata['enabled_entitlement_count'] ?? null,
            'enabled_feature_count' => $metadata['enabled_feature_count'] ?? null,
            'failed_reason_code' => $metadata['failed_reason_code'] ?? null,
            'domain_present' => $metadata['domain_present'] ?? null,
        ], fn ($value) => $value !== null), request: $request);
    }

    private function enabledCount(array $values): int
    {
        return collect($values)
            ->filter(fn (mixed $value): bool => is_array($value) ? (bool) ($value['enabled'] ?? false) : (bool) $value)
            ->count();
    }
}
