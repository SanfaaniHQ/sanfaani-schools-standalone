<?php

namespace App\Http\Controllers\Demo;

use App\Http\Controllers\Controller;
use App\Services\Demo\MarketplaceLiveDemoService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class MarketplaceLiveDemoController extends Controller
{
    public function index(MarketplaceLiveDemoService $demo): View
    {
        abort_unless($demo->enabled(), 404);

        return view('demo.live', [
            'accounts' => $demo->publicAccounts(),
            'autoLoginEnabled' => $demo->autoLoginEnabled(),
            'resetHours' => $demo->resetHours(),
            'safeModeEnabled' => $demo->safeModeEnabled(),
        ]);
    }

    public function login(Request $request, string $role, MarketplaceLiveDemoService $demo): RedirectResponse
    {
        abort_unless($demo->autoLoginEnabled(), 404);

        $user = $demo->knownPublicDemoUser($role);

        abort_unless($user, 404);

        Auth::login($user);
        $request->session()->regenerate();
        $demo->selectWorkspace($user, $role);

        return redirect()->route('dashboard');
    }
}
