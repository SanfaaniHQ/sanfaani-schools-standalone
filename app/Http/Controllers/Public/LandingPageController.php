<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\LeadRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

class LandingPageController extends Controller
{
    private const SUCCESS_MESSAGE = 'Thank you. We have received your request and will contact you.';

    public function home()
    {
        return view('public.landing.home');
    }

    public function features()
    {
        return view('public.landing.features');
    }

    public function pricing()
    {
        return view('public.landing.pricing');
    }

    public function contact()
    {
        return view('public.landing.contact');
    }

    public function submitContact(Request $request)
    {
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

        return back()->with('success', self::SUCCESS_MESSAGE);
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

        return back()->with('success', self::SUCCESS_MESSAGE);
    }

    private function storeLead(string $type, array $data, string $source, array $metadata = []): void
    {
        if (! Schema::hasTable('lead_requests')) {
            return;
        }

        LeadRequest::create([
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
    }
}
