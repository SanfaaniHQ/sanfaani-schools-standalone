<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\LeadRequest;
use App\Models\User;
use App\Services\CommunicationService;
use App\Services\Installer\InstallerStateService;
use App\Services\LeadCrmService;
use App\Services\Standalone\StandaloneEditionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class LandingPageController extends Controller
{
    public function home(StandaloneEditionService $standalone, InstallerStateService $installer): View|RedirectResponse
    {
        if ($standalone->privateHomepageEnabled()) {
            return $installer->isInstalled()
                ? redirect()->route('login')
                : redirect()->route('installer.welcome');
        }

        return view('public.landing.home');
    }

    public function features(StandaloneEditionService $standalone)
    {
        abort_if($standalone->hidesPlatformMarketingSurfaces(), 404);

        return view('public.landing.features');
    }

    public function pricing(StandaloneEditionService $standalone)
    {
        abort_if($standalone->hidesSaasSurfaces(), 404);

        return view('public.landing.pricing');
    }

    public function contact(StandaloneEditionService $standalone)
    {
        abort_if($standalone->hidesPlatformMarketingSurfaces(), 404);

        return view('public.landing.contact');
    }

    public function submitContact(Request $request, StandaloneEditionService $standalone)
    {
        abort_if($standalone->hidesPlatformMarketingSurfaces(), 404);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'school_name' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'role' => ['nullable', 'string', 'max:100'],
            'message' => ['nullable', 'string', 'max:3000'],
        ]);

        $this->storeLead('contact', $data, 'landing_contact', [
            'page' => 'contact',
        ]);

        return back()->with('success', __('marketing.lead_success'));
    }

    public function demo()
    {
        return view('public.landing.demo');
    }

    public function submitDemo(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'school_name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'number_of_students' => ['nullable', 'integer', 'min:1', 'max:100000'],
            'school_type' => [
                'nullable',
                Rule::in(['conventional', 'islamic', 'madrasah', 'mixed', 'training_center']),
            ],
            'preferred_demo_time' => ['nullable', 'string', 'max:150'],
            'message' => ['nullable', 'string', 'max:3000'],
        ]);

        $this->storeLead('demo', [
            'name' => $data['name'],
            'school_name' => $data['school_name'],
            'phone' => $data['phone'],
            'email' => $data['email'] ?? null,
            'role' => null,
            'number_of_students' => $data['number_of_students'] ?? null,
            'school_type' => $data['school_type'] ?? null,
            'preferred_demo_time' => $data['preferred_demo_time'] ?? null,
            'message' => $data['message'] ?? null,
        ], 'landing_demo');

        return back()->with('success', __('marketing.lead_success'));
    }

    private function storeLead(string $type, array $data, string $source, array $metadata = []): void
    {
        if (! Schema::hasTable('lead_requests')) {
            return;
        }

        $lead = LeadRequest::create([
            'type' => $type,
            'name' => $data['name'],
            'school_name' => $data['school_name'] ?? null,
            'email' => $data['email'] ?? null,
            'phone' => $data['phone'] ?? null,
            'role' => $data['role'] ?? null,
            'number_of_students' => $data['number_of_students'] ?? null,
            'school_type' => $data['school_type'] ?? null,
            'preferred_demo_time' => $data['preferred_demo_time'] ?? null,
            'message' => $data['message'] ?? null,
            'source' => $source,
            'status' => 'new',
            'metadata' => array_filter($metadata, fn ($value) => filled($value)),
        ]);

        app(LeadCrmService::class)->recordSystemEvent($lead, 'created', 'Lead request submitted', ucfirst($type).' request received.', [
            'source' => $source,
            'lead_type' => $type,
        ]);

        if (filled($data['email'] ?? null)) {
            app(CommunicationService::class)->sendPlatformEmail(
                $data['email'],
                __('marketing.lead_ack_subject'),
                __('marketing.lead_ack_title'),
                __('marketing.lead_ack_body'),
                'lead_acknowledgment',
                ['lead_type' => $type],
                'platform_transactional'
            );
        }

        User::role('super_admin')
            ->select('id', 'email')
            ->whereNotNull('email')
            ->chunkById(50, function ($admins) use ($data, $type) {
                foreach ($admins as $admin) {
                    app(CommunicationService::class)->sendPlatformEmail(
                        $admin->email,
                        __('marketing.lead_admin_subject', ['type' => ucfirst($type)]),
                        __('marketing.lead_admin_title'),
                        __('marketing.lead_admin_body', ['type' => $type, 'name' => $data['name']]),
                        'lead_admin_notification',
                        ['lead_type' => $type, 'requester_email' => $data['email'] ?? null],
                        'platform_transactional'
                    );
                }
            });
    }
}
