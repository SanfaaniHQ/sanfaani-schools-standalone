<?php

namespace App\Http\Controllers;

use App\Services\Marketing\UnsubscribeService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MarketingUnsubscribeController extends Controller
{
    public function __invoke(string $token, Request $request, UnsubscribeService $unsubscribes): View
    {
        $unsubscribes->recordFromToken($token, [
            'ip_hash' => hash('sha256', (string) $request->ip()),
            'source' => 'public_unsubscribe_route',
        ]);

        return view('marketing.unsubscribe');
    }
}
