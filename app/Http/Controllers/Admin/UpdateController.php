<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\UpdateLog;
use App\Models\UpdatePackage;
use App\Services\AuditLogService;
use App\Services\Updates\SystemVersionService;
use App\Services\Updates\UpdateEntitlementService;
use App\Services\Updates\UpdateManifestService;
use App\Services\Updates\UpdatePackageService;
use App\Services\Updates\UpdatePreflightService;
use App\Services\Updates\UpdateServerClient;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use RuntimeException;

class UpdateController extends Controller
{
    public function __construct(
        private SystemVersionService $versions,
        private UpdateEntitlementService $entitlements,
        private UpdateManifestService $manifests,
        private UpdatePackageService $packages,
        private UpdatePreflightService $preflight,
        private UpdateServerClient $client,
        private AuditLogService $auditLog,
    ) {}

    public function index(): View
    {
        $decision = $this->authorizeUpdateAccess();
        $currentVersion = $this->versions->recordCurrent();

        return view('admin.updates.index', [
            'label' => $decision['label'],
            'decision' => $decision,
            'currentVersion' => $currentVersion,
            'packages' => UpdatePackage::with(['uploadedBy', 'rollbackPlan'])
                ->latest()
                ->paginate(10),
            'logs' => UpdateLog::with(['updatePackage', 'creator'])
                ->latest()
                ->limit(10)
                ->get(),
            'channel' => (string) config('updates.channel', 'stable'),
        ]);
    }

    public function check(): View
    {
        $decision = $this->authorizeUpdateAccess();
        $result = $this->client->checkForUpdates();

        return view('admin.updates.check', [
            'label' => $decision['label'],
            'decision' => $decision,
            'result' => $result,
        ]);
    }

    public function upload(): View
    {
        $decision = $this->authorizeUpdateAccess();

        return view('admin.updates.upload', [
            'label' => $decision['label'],
            'decision' => $decision,
            'sampleManifest' => json_encode($this->manifests->sample(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES),
            'maxPackageMb' => (int) config('updates.max_package_mb', 50),
            'uploadsAllowed' => (bool) config('updates.allow_package_upload', true),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizeUpdateAccess();

        $data = $request->validate($this->packages->validationRules());
        $manifest = $this->manifests->parseJson($data['manifest_json'] ?? null);
        $package = $this->packages->storeUploadedPackage($data['package'], $manifest, $request->user());
        $this->auditLog->log('update_package_uploaded', $package, metadata: [
            'version' => $package->version,
            'channel' => $package->channel,
            'status' => $package->status,
        ], request: $request);

        return redirect()
            ->route('admin.updates.show', $package)
            ->with('success', 'Update package metadata was stored safely. No files were extracted or applied.');
    }

    public function show(UpdatePackage $updatePackage): View
    {
        $decision = $this->authorizeUpdateAccess();
        $updatePackage->load(['uploadedBy', 'logs.creator', 'rollbackPlan']);

        return view('admin.updates.show', [
            'label' => $decision['label'],
            'decision' => $decision,
            'updatePackage' => $updatePackage,
            'preflight' => data_get($updatePackage->metadata, 'preflight'),
        ]);
    }

    public function preflight(UpdatePackage $updatePackage): RedirectResponse
    {
        $this->authorizeUpdateAccess();

        $result = $this->preflight->run($updatePackage, auth()->user());
        $this->auditLog->log('update_preflight_run', $updatePackage->fresh(), metadata: [
            'passed' => $result->passed(),
            'summary' => $result->summary(),
        ], request: request());

        return redirect()
            ->route('admin.updates.show', $updatePackage)
            ->with($result->passed() ? 'success' : 'error', $result->summary());
    }

    public function markReady(UpdatePackage $updatePackage): RedirectResponse
    {
        $this->authorizeUpdateAccess();

        try {
            $package = $this->packages->markReady($updatePackage, auth()->user());
        } catch (RuntimeException $exception) {
            return back()->with('error', $exception->getMessage());
        }
        $this->auditLog->log('update_package_marked_ready', $package, metadata: [
            'version' => $package->version,
            'status' => $package->status,
            'application_performed' => false,
        ], request: request());

        return redirect()
            ->route('admin.updates.show', $package)
            ->with('success', 'Package is ready for manual update planning. No update was applied.');
    }

    private function authorizeUpdateAccess(): array
    {
        $decision = $this->entitlements->check(auth()->user());

        abort_unless($decision['allowed'], 403, $decision['message']);

        return $decision;
    }
}
