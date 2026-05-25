<?php

namespace App\Http\Controllers\Demo;

use App\Http\Controllers\Controller;
use App\Jobs\Demo\CreateDemoEnvironmentJob;
use App\Services\Demo\DemoEnvironmentService;
use App\Services\Demo\DemoRequestService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DemoRequestController extends Controller
{
    public function create(DemoEnvironmentService $environment)
    {
        abort_unless($environment->canAccessDemo(), 404);

        return view('demo.request', [
            'roleOptions' => config('demo.roles', []),
        ]);
    }

    public function store(
        Request $request,
        DemoRequestService $requests,
        DemoEnvironmentService $environment
    ) {
        abort_unless($environment->canAccessDemo(), 404);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'school_name' => ['nullable', 'string', 'max:255'],
            'role_interest' => ['nullable', 'string', Rule::in(array_keys(config('demo.roles', [])))],
            'school_type' => ['nullable', 'string', 'max:100'],
            'source' => ['nullable', 'string', 'max:100'],
            'message' => ['nullable', 'string', 'max:3000'],
        ]);

        if (filled($data['school_type'] ?? null)) {
            data_set($data, 'metadata.school_type', $data['school_type']);
        }

        $demoRequest = $requests->create($data + ['source' => 'public_demo']);

        CreateDemoEnvironmentJob::dispatch($demoRequest);

        return redirect()
            ->route('demo.thank-you')
            ->with('success', 'Your demo environment is being prepared.');
    }

    public function thankYou()
    {
        return view('demo.thank-you');
    }
}
