<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LeadRequest;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class LeadRequestController extends Controller
{
    public function index(Request $request)
    {
        return view('admin.lead-requests.index', [
            'leads' => LeadRequest::query()
                ->when($request->filled('type'), fn ($query) => $query->where('type', $request->input('type')))
                ->when($request->filled('status'), fn ($query) => $query->where('status', $request->input('status')))
                ->latest()
                ->paginate(15)
                ->withQueryString(),
            'type' => $request->input('type'),
            'status' => $request->input('status'),
        ]);
    }

    public function show(LeadRequest $leadRequest)
    {
        return view('admin.lead-requests.show', [
            'lead' => $leadRequest,
        ]);
    }

    public function update(Request $request, LeadRequest $leadRequest)
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(['new', 'contacted', 'converted', 'closed'])],
            'notes' => ['nullable', 'string', 'max:5000'],
        ]);

        $metadata = $leadRequest->metadata ?? [];

        $leadRequest->update([
            'status' => $data['status'],
            'notes' => $data['notes'] ?? null,
            'contacted_at' => $data['status'] === 'contacted' && ! $leadRequest->contacted_at ? now() : $leadRequest->contacted_at,
            'metadata' => array_filter($metadata, fn ($value) => filled($value)),
        ]);

        return redirect()
            ->route('admin.lead-requests.show', $leadRequest)
            ->with('success', 'Lead request updated.');
    }
}
