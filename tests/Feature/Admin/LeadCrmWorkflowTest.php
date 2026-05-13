<?php

namespace Tests\Feature\Admin;

use App\Mail\Transactional\PlatformTransactionalMail;
use App\Models\CommunicationLog;
use App\Models\LeadCommunicationRecord;
use App\Models\LeadNote;
use App\Models\LeadOwnershipHistory;
use App\Models\LeadRequest;
use App\Models\LeadTimelineEvent;
use App\Models\School;
use App\Models\User;
use App\Services\LeadCrmService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class LeadCrmWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
        Role::findOrCreate('super_admin');
        Role::findOrCreate('admin');
    }

    public function test_public_contact_flow_still_creates_a_backward_compatible_lead(): void
    {
        Mail::fake();

        $response = $this->from('/contact')->post(route('landing.contact.submit'), [
            'name' => 'Aisha Bello',
            'school_name' => 'Aisha Model School',
            'email' => 'aisha@example.test',
            'phone' => '08030000000',
            'role' => 'Director',
            'message' => 'We want to see a demo.',
        ]);

        $response->assertRedirect('/contact');
        $response->assertSessionHas('success');

        $lead = LeadRequest::firstOrFail();

        $this->assertSame('contact', $lead->type);
        $this->assertSame(LeadRequest::STATUS_NEW, $lead->status);
        $this->assertSame('Aisha Model School', $lead->school_name);
        $this->assertDatabaseHas('lead_timeline_events', [
            'lead_request_id' => $lead->id,
            'event_type' => 'created',
        ]);
        Mail::assertSent(PlatformTransactionalMail::class);
    }

    public function test_admin_can_update_status_owner_follow_up_and_private_notes_transactionally(): void
    {
        $admin = $this->superAdmin();
        $owner = User::factory()->create(['name' => 'CRM Staff', 'email' => 'crm@example.test']);
        $lead = $this->lead(['status' => LeadRequest::STATUS_NEW]);

        $this->actingAs($admin);

        $response = $this->patch(route('admin.lead-requests.update', $lead), [
            'status' => LeadRequest::STATUS_FOLLOW_UP,
            'assigned_to' => $owner->id,
            'next_follow_up_at' => now()->addDay()->format('Y-m-d H:i:s'),
            'notes' => 'Legacy note remains visible.',
            'note_body' => 'Called the proprietor and agreed on a demo slot.',
        ]);

        $response->assertRedirect(route('admin.lead-requests.show', $lead));

        $lead->refresh();

        $this->assertSame(LeadRequest::STATUS_FOLLOW_UP, $lead->status);
        $this->assertSame($owner->id, $lead->assigned_to);
        $this->assertNotNull($lead->next_follow_up_at);
        $this->assertSame('Called the proprietor and agreed on a demo slot.', $lead->notes);
        $this->assertSame(1, LeadOwnershipHistory::where('lead_request_id', $lead->id)->count());
        $this->assertSame(1, LeadNote::where('lead_request_id', $lead->id)->count());
        $this->assertDatabaseHas('lead_timeline_events', ['lead_request_id' => $lead->id, 'event_type' => 'status_changed']);
        $this->assertDatabaseHas('lead_timeline_events', ['lead_request_id' => $lead->id, 'event_type' => 'ownership_changed']);
        $this->assertDatabaseHas('lead_timeline_events', ['lead_request_id' => $lead->id, 'event_type' => 'note_added']);
        $this->assertDatabaseHas('audit_logs', ['action' => 'lead_status_changed', 'auditable_id' => $lead->id]);
        $this->assertDatabaseHas('audit_logs', ['action' => 'lead_ownership_changed', 'auditable_id' => $lead->id]);
    }

    public function test_admin_can_open_the_lead_crm_workspace(): void
    {
        $admin = $this->superAdmin();
        $lead = $this->lead(['name' => 'Workspace Lead']);

        $this->actingAs($admin);

        $response = $this->get(route('admin.lead-requests.show', $lead));

        $response->assertOk();
        $response->assertSee('Workspace Lead');
        $response->assertSee('Timeline');
        $response->assertSee('Communication History');
        $response->assertSee('Conversion');
    }

    public function test_lead_conversion_creates_one_school_and_preserves_original_lead_snapshot(): void
    {
        $admin = $this->superAdmin();
        $lead = $this->lead([
            'name' => 'Fatima Musa',
            'school_name' => 'Future Stars Academy',
            'email' => 'future@example.test',
            'phone' => '08040000000',
        ]);

        $this->actingAs($admin);

        $school = app(LeadCrmService::class)->convertToSchool($lead, $admin);
        $sameSchool = app(LeadCrmService::class)->convertToSchool($lead->fresh(), $admin);

        $lead->refresh();

        $this->assertTrue($school->is($sameSchool));
        $this->assertSame(1, School::count());
        $this->assertSame('Future Stars Academy', $school->name);
        $this->assertSame('trial', $school->subscription_status);
        $this->assertSame(LeadRequest::STATUS_CONVERTED, $lead->status);
        $this->assertSame($school->id, $lead->converted_school_id);
        $this->assertSame('future@example.test', data_get($lead->metadata, 'conversion.original_lead_snapshot.email'));
        $this->assertDatabaseHas('lead_timeline_events', ['lead_request_id' => $lead->id, 'event_type' => 'converted']);
        $this->assertDatabaseHas('audit_logs', ['action' => 'lead_converted', 'auditable_id' => $lead->id]);
    }

    public function test_admin_lead_filters_support_status_owner_conversion_dates_and_search(): void
    {
        $admin = $this->superAdmin();
        $owner = User::factory()->create(['name' => 'Assigned Owner']);
        $matching = $this->lead([
            'name' => 'Alpha Contact',
            'school_name' => 'Alpha School',
            'status' => LeadRequest::STATUS_FOLLOW_UP,
            'assigned_to' => null,
        ]);
        $this->lead([
            'name' => 'Beta Contact',
            'school_name' => 'Beta School',
            'status' => LeadRequest::STATUS_FOLLOW_UP,
            'assigned_to' => $owner->id,
        ]);

        $this->actingAs($admin);

        $response = $this->get(route('admin.lead-requests.index', [
            'status' => LeadRequest::STATUS_FOLLOW_UP,
            'assigned_to' => 'unassigned',
            'conversion' => 'unconverted',
            'search' => 'Alpha',
            'date_from' => now()->subDay()->toDateString(),
            'date_to' => now()->addDay()->toDateString(),
        ]));

        $response->assertOk();
        $response->assertSee($matching->name);
        $response->assertDontSee('Beta Contact');
    }

    public function test_platform_lead_email_is_logged_into_lead_communication_history(): void
    {
        Mail::fake();
        $admin = $this->superAdmin();
        $lead = $this->lead(['email' => 'lead@example.test']);

        $this->actingAs($admin);

        $response = $this->post(route('admin.communications.send'), [
            'target' => 'lead',
            'lead_id' => $lead->id,
            'subject' => 'Demo follow-up',
            'message' => 'Please choose a demo slot.',
        ]);

        $response->assertRedirect();
        $this->assertSame(1, CommunicationLog::count());
        $this->assertSame(1, LeadCommunicationRecord::where('lead_request_id', $lead->id)->count());
        $this->assertSame(1, LeadTimelineEvent::where('lead_request_id', $lead->id)->where('event_type', 'communication_recorded')->count());
        Mail::assertSent(PlatformTransactionalMail::class);
    }

    private function superAdmin(): User
    {
        $user = User::factory()->create([
            'name' => 'Super Admin',
            'email' => fake()->unique()->safeEmail(),
        ]);
        $user->assignRole('super_admin');

        return $user;
    }

    private function lead(array $attributes = []): LeadRequest
    {
        return LeadRequest::create(array_merge([
            'type' => 'demo',
            'name' => 'Lead Contact',
            'school_name' => 'Lead School',
            'email' => 'lead-contact@example.test',
            'phone' => '08020000000',
            'status' => LeadRequest::STATUS_NEW,
            'source' => 'test',
            'metadata' => [],
        ], $attributes));
    }
}
