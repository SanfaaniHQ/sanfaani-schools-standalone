<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DemoSession;
use App\Services\Demo\DemoCredentialService;
use App\Services\Demo\DemoExpiryService;
use Illuminate\Http\RedirectResponse;

class DemoSessionController extends Controller
{
    public function index()
    {
        $sessions = DemoSession::query()
            ->with(['demoRequest', 'school'])
            ->latest()
            ->paginate(20);

        return view('admin.demo.index', [
            'sessions' => $sessions,
            'activeCount' => DemoSession::active()->count(),
            'maxActiveSessions' => (int) config('demo.max_active_sessions', 25),
        ]);
    }

    public function show(DemoSession $demoSession)
    {
        $demoSession->load(['demoRequest', 'school', 'license', 'credentials.user', 'activities.user']);

        return view('admin.demo.show', [
            'demoSession' => $demoSession,
            'revealedCredentials' => session('revealedCredentials', []),
        ]);
    }

    public function expire(DemoSession $demoSession, DemoExpiryService $expiry): RedirectResponse
    {
        $expiry->expire($demoSession, manual: true, actor: auth()->user());

        return redirect()
            ->route('admin.demo.show', $demoSession)
            ->with('success', 'Demo session expired and temporary credentials were revoked.');
    }

    public function credentials(DemoSession $demoSession, DemoCredentialService $credentials): RedirectResponse
    {
        $demoSession->loadMissing('credentials.demoSession');

        $revealed = $demoSession->credentials
            ->mapWithKeys(function ($credential) use ($credentials): array {
                $password = $credentials->revealOnce($credential);

                return $password === null ? [] : [
                    $credential->id => [
                        'label' => $credential->label,
                        'email' => $credential->email,
                        'password' => $password,
                    ],
                ];
            })
            ->all();

        return redirect()
            ->route('admin.demo.show', $demoSession)
            ->with($revealed === [] ? 'error' : 'success', $revealed === [] ? 'No unviewed active demo credentials are available.' : 'Temporary demo credentials are displayed once.')
            ->with('revealedCredentials', $revealed);
    }
}
