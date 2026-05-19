<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MarketingEmailTemplate;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class MarketingEmailTemplateController extends Controller
{
    public function index(): View
    {
        return view('admin.email-marketing.templates.index', [
            'templates' => MarketingEmailTemplate::latest()->paginate(15),
        ]);
    }

    public function create(): View
    {
        return view('admin.email-marketing.templates.form', [
            'template' => new MarketingEmailTemplate(['status' => MarketingEmailTemplate::STATUS_ACTIVE]),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $data['slug'] = $this->uniqueSlug($data['name']);
        $data['created_by'] = $request->user()->id;
        $data['updated_by'] = $request->user()->id;

        MarketingEmailTemplate::create($data);

        return redirect()
            ->route('admin.email-marketing.templates.index')
            ->with('success', 'Email template saved.');
    }

    public function edit(MarketingEmailTemplate $template): View
    {
        return view('admin.email-marketing.templates.form', compact('template'));
    }

    public function update(Request $request, MarketingEmailTemplate $template): RedirectResponse
    {
        $data = $this->validated($request);
        $data['updated_by'] = $request->user()->id;

        $template->update($data);

        return redirect()
            ->route('admin.email-marketing.templates.index')
            ->with('success', 'Email template updated.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'subject' => ['required', 'string', 'max:255'],
            'preview_text' => ['nullable', 'string', 'max:255'],
            'body' => ['required', 'string'],
            'status' => ['required', Rule::in([MarketingEmailTemplate::STATUS_ACTIVE, MarketingEmailTemplate::STATUS_ARCHIVED])],
        ]);
    }

    private function uniqueSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'template';
        $slug = $base;
        $counter = 2;

        while (MarketingEmailTemplate::where('slug', $slug)->exists()) {
            $slug = "{$base}-{$counter}";
            $counter++;
        }

        return $slug;
    }
}
