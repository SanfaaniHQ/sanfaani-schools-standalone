<?php

namespace Tests\Feature\School;

use App\Models\AcademicSession;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\ScratchCardBatch;
use App\Models\Term;
use App\Models\User;
use App\Models\UserSchoolRole;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class ScratchCardDirectGenerationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (['super_admin', 'school_admin', 'teacher', 'result_officer', 'accountant', 'parent', 'student'] as $role) {
            Role::findOrCreate($role);
        }
    }

    public function test_school_admin_can_generate_scratch_cards_directly(): void
    {
        $context = $this->schoolContext();
        $this->actAsSchoolRole($context['user'], $context['school'], 'school_admin');

        $this->post(route('school.scratch-cards.store'), $this->validPayload($context, [
            'generation_mode' => 'direct',
            'quantity' => 5,
            'max_uses' => 2,
        ]))->assertRedirect();

        $batch = ScratchCardBatch::firstOrFail();

        $this->assertSame('generated', $batch->status);
        $this->assertSame('paid', $batch->payment_status);
        $this->assertNotNull($batch->payment_confirmed_at);
        $this->assertSame($context['user']->id, $batch->generated_by);
        $this->assertSame(5, $batch->cards()->count());
        $this->assertDatabaseHas('scratch_cards', [
            'scratch_card_batch_id' => $batch->id,
            'school_id' => $context['school']->id,
            'max_uses' => 2,
            'status' => 'unused',
        ]);
    }

    public function test_school_admin_can_generate_existing_paid_batch(): void
    {
        $context = $this->schoolContext();
        $batch = $this->scratchBatch($context, [
            'payment_status' => 'paid',
            'payment_confirmed_at' => now(),
            'payment_confirmed_by' => $context['user']->id,
            'status' => 'approved',
            'approved_at' => now(),
            'approved_by' => $context['user']->id,
        ]);
        $this->actAsSchoolRole($context['user'], $context['school'], 'school_admin');

        $this->post(route('school.scratch-cards.generate', $batch), [
            'max_uses' => 3,
        ])->assertRedirect();

        $this->assertSame('generated', $batch->fresh()->status);
        $this->assertSame(4, $batch->cards()->count());
        $this->assertDatabaseHas('audit_logs', [
            'school_id' => $context['school']->id,
            'action' => 'scratch_card_batch_generated',
        ]);
    }

    public function test_unpaid_existing_batch_cannot_be_generated_from_school_portal(): void
    {
        $context = $this->schoolContext();
        $batch = $this->scratchBatch($context, [
            'payment_status' => 'pending',
            'payment_confirmed_at' => null,
            'status' => 'pending_payment',
        ]);
        $this->actAsSchoolRole($context['user'], $context['school'], 'school_admin');

        $this->from(route('school.scratch-cards.show', $batch))
            ->post(route('school.scratch-cards.generate', $batch), [
                'max_uses' => 1,
            ])
            ->assertRedirect(route('school.scratch-cards.show', $batch))
            ->assertSessionHasErrors('max_uses');

        $this->assertDatabaseCount('scratch_cards', 0);
    }

    private function schoolContext(): array
    {
        $school = $this->createSchool();
        $class = SchoolClass::create([
            'school_id' => $school->id,
            'name' => 'JSS 1',
            'section' => fake()->unique()->lexify('S??'),
            'status' => 'active',
        ]);
        $session = AcademicSession::create([
            'school_id' => $school->id,
            'name' => fake()->unique()->numerify('2026/2027 ###'),
            'is_active' => true,
            'status' => 'active',
        ]);
        $term = Term::create([
            'school_id' => $school->id,
            'academic_session_id' => $session->id,
            'name' => 'First Term '.fake()->unique()->numberBetween(1, 9999),
            'is_active' => true,
            'status' => 'active',
        ]);
        $user = $this->createUserForSchool($school, 'school_admin');

        return compact('school', 'class', 'session', 'term', 'user');
    }

    private function validPayload(array $context, array $overrides = []): array
    {
        return array_merge([
            'title' => 'Direct First Term Cards',
            'school_class_id' => $context['class']->id,
            'academic_session_id' => $context['session']->id,
            'term_id' => $context['term']->id,
            'result_type' => 'term_result',
            'quantity' => 4,
            'generation_mode' => 'direct',
            'max_uses' => 1,
            'payment_method' => 'manual',
            'payment_reference' => 'DIRECT',
            'request_note' => null,
        ], $overrides);
    }

    private function scratchBatch(array $context, array $overrides = []): ScratchCardBatch
    {
        return ScratchCardBatch::create(array_merge([
            'school_id' => $context['school']->id,
            'requested_by' => $context['user']->id,
            'school_class_id' => $context['class']->id,
            'academic_session_id' => $context['session']->id,
            'term_id' => $context['term']->id,
            'result_type' => 'term_result',
            'title' => 'Existing Scratch Batch',
            'quantity' => 4,
            'amount' => 0,
            'currency' => 'NGN',
            'payment_status' => 'pending',
            'payment_method' => 'manual',
            'status' => 'pending_payment',
            'metadata' => [],
        ], $overrides));
    }

    private function createSchool(): School
    {
        $id = fake()->unique()->numberBetween(1, 999999);

        return School::create([
            'name' => 'Sanfaani Scratch Academy '.$id,
            'slug' => 'sanfaani-scratch-academy-'.$id,
            'status' => 'active',
            'subscription_status' => 'active',
        ]);
    }

    private function createUserForSchool(School $school, string $role): User
    {
        $user = User::factory()->create([
            'school_id' => $school->id,
            'email' => fake()->unique()->safeEmail(),
        ]);
        $user->assignRole($role);

        UserSchoolRole::create([
            'user_id' => $user->id,
            'school_id' => $school->id,
            'role_name' => $role,
            'status' => 'active',
        ]);

        return $user;
    }

    private function actAsSchoolRole(User $user, School $school, string $role): void
    {
        $this->actingAs($user);
        session([
            'active_school_id' => $school->id,
            'active_role_context' => $role,
        ]);
    }
}
