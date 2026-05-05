<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SystemUpdateLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

class SystemUpdateController extends Controller
{
    public function index()
    {
        return view('admin.system-updates.index', [
            'currentVersion' => config('version.version'),
            'productName' => config('version.product_name'),
            'updates' => SystemUpdateLog::with('uploadedBy')->latest()->paginate(15),
        ]);
    }

    public function upload(Request $request)
    {
        $data = $request->validate([
            'package' => ['required', 'file', 'mimes:zip', 'max:51200'],
            'notes' => ['nullable', 'string', 'max:5000'],
        ]);

        $file = $request->file('package');
        $manifest = $this->manifestFromZip($file->getRealPath());
        $checksum = hash_file('sha256', $file->getRealPath());
        $currentVersion = config('version.version');

        $errors = $this->validateManifest($manifest, $checksum, $currentVersion);

        if ($errors !== []) {
            return back()->withInput()->with('upload_error', implode(' ', $errors));
        }

        $path = $file->store('', 'updates');

        SystemUpdateLog::create([
            'from_version' => $currentVersion,
            'to_version' => $manifest['version'] ?? null,
            'update_type' => 'manual_package_upload',
            'status' => 'validated',
            'uploaded_by' => auth()->id(),
            'package_path' => $path,
            'notes' => $data['notes'] ?? null,
            'metadata' => [
                'safe_mode' => true,
                'automatic_apply' => false,
                'checksum_sha256' => $checksum,
                'manifest' => $manifest,
                'storage_disk' => 'updates',
                'private_path' => Storage::disk('updates')->path($path),
            ],
        ]);

        return redirect()
            ->route('admin.system-updates.index')
            ->with('success', 'Update package validated and stored privately. It was not applied automatically.');
    }

    private function manifestFromZip(string $path): ?array
    {
        $zip = new ZipArchive();

        if ($zip->open($path) !== true) {
            return null;
        }

        $manifestContents = $zip->getFromName('manifest.json');
        $zip->close();

        if ($manifestContents === false) {
            return null;
        }

        $manifest = json_decode($manifestContents, true);

        return is_array($manifest) ? $manifest : null;
    }

    private function validateManifest(?array $manifest, string $checksum, string $currentVersion): array
    {
        if (! $manifest) {
            return ['The update package must contain a readable manifest.json file.'];
        }

        $required = ['product_name', 'version', 'min_version', 'release_date', 'checksum', 'vendor', 'notes'];
        $errors = [];

        foreach ($required as $field) {
            if (! filled($manifest[$field] ?? null)) {
                $errors[] = "manifest.json is missing {$field}.";
            }
        }

        if (($manifest['product_name'] ?? null) !== 'Sanfaani Schools') {
            $errors[] = 'The package product_name must be Sanfaani Schools.';
        }

        if (filled($manifest['version'] ?? null) && version_compare((string) $manifest['version'], $currentVersion, '<=')) {
            $errors[] = 'The package version must be newer than the current application version.';
        }

        if (filled($manifest['min_version'] ?? null) && version_compare($currentVersion, (string) $manifest['min_version'], '<')) {
            $errors[] = 'The current application version is lower than the package minimum version.';
        }

        if (filled($manifest['checksum'] ?? null) && ! hash_equals(strtolower((string) $manifest['checksum']), strtolower($checksum))) {
            $errors[] = 'The package SHA-256 checksum does not match manifest.json.';
        }

        return $errors;
    }
}
