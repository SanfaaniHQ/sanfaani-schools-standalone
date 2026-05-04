<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SystemUpdateLog;
use Illuminate\Http\Request;

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
            'to_version' => ['nullable', 'string', 'max:50'],
            'package' => ['required', 'file', 'mimes:zip', 'max:51200'],
            'notes' => ['nullable', 'string', 'max:5000'],
        ]);

        $path = $request->file('package')->store('', 'updates');

        SystemUpdateLog::create([
            'from_version' => config('version.version'),
            'to_version' => $data['to_version'] ?? null,
            'update_type' => 'manual_package_upload',
            'status' => 'uploaded',
            'uploaded_by' => auth()->id(),
            'package_path' => $path,
            'notes' => $data['notes'] ?? null,
            'metadata' => [
                'safe_mode' => true,
                'automatic_apply' => false,
            ],
        ]);

        return redirect()
            ->route('admin.system-updates.index')
            ->with('success', 'Update package uploaded safely. Review and backup before applying manually.');
    }
}
