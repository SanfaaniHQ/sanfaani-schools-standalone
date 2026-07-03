<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-xl font-semibold leading-tight text-text-primary">Marketing Pipeline</h2>
            <p class="mt-1 text-sm text-text-secondary">Lead nurturing, sales tasks, and conversion analytics.</p>
        </div>
    </x-slot>

    <div class="space-y-6">
        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <x-ui.stat-card label="New Leads" :value="$analytics['new_leads']" meta="Unworked CRM leads" />
            <x-ui.stat-card label="Demo Requests" :value="$analytics['demo_requests']" meta="Captured public demos" />
            <x-ui.stat-card label="Active Demo Sessions" :value="$analytics['active_demo_sessions']" meta="Evaluation spaces" />
            <x-ui.stat-card label="Pending Sales Tasks" :value="$analytics['pending_sales_tasks']" meta="Needs follow-up" tone="warning" />
            <x-ui.stat-card label="Trial Leads" :value="$analytics['trial_leads']" meta="Trial-stage prospects" />
            <x-ui.stat-card label="Conversion Milestones" :value="$analytics['conversion_milestones']" meta="Onboarding and conversion signals" tone="success" />
            <x-ui.stat-card label="WhatsApp Hooks" :value="config('marketing.whatsapp_enabled') ? 'Enabled' : 'Needs attention'" meta="Provider integration is configured outside this screen" />
        </section>

        <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            <a href="{{ route('admin.marketing.leads') }}" class="ui-card ui-card-hover block p-5">
                <span class="font-semibold text-text-primary">Lead Scores</span>
                <span class="mt-1 block text-sm text-text-secondary">View CRM leads with scoring signals.</span>
            </a>
            <a href="{{ route('admin.marketing.activities') }}" class="ui-card ui-card-hover block p-5">
                <span class="font-semibold text-text-primary">Activities</span>
                <span class="mt-1 block text-sm text-text-secondary">Review demo, onboarding, and CRM signals.</span>
            </a>
            <a href="{{ route('admin.marketing.sequences') }}" class="ui-card ui-card-hover block p-5">
                <span class="font-semibold text-text-primary">Sequences</span>
                <span class="mt-1 block text-sm text-text-secondary">Inspect nurture sequence definitions.</span>
            </a>
            <a href="{{ route('admin.sales.tasks.index') }}" class="ui-card ui-card-hover block p-5">
                <span class="font-semibold text-text-primary">Sales Tasks</span>
                <span class="mt-1 block text-sm text-text-secondary">Follow-up queue for sales operations.</span>
            </a>
        </section>
    </div>
</x-app-layout>
