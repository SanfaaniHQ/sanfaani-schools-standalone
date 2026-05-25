<?php

namespace Tests\Feature\Marketing;

use App\Models\LeadRequest;
use App\Models\SalesTask;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class SalesTaskTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
        Role::findOrCreate('super_admin');
        Role::findOrCreate('school_admin');
        $this->configureMarketing();
    }

    public function test_sales_task_can_be_completed_by_authorized_admin(): void
    {
        $admin = $this->superAdmin();
        $task = $this->task();

        $this->actingAs($admin)
            ->post(route('admin.sales.tasks.complete', $task))
            ->assertRedirect();

        $this->assertDatabaseHas('sales_tasks', [
            'id' => $task->id,
            'status' => SalesTask::STATUS_COMPLETED,
        ]);
    }

    public function test_unauthorized_user_cannot_complete_sales_task(): void
    {
        $user = User::factory()->create();
        $user->assignRole('school_admin');
        $task = $this->task();

        $this->actingAs($user)
            ->post(route('admin.sales.tasks.complete', $task))
            ->assertForbidden();
    }

    private function task(): SalesTask
    {
        $lead = LeadRequest::create([
            'type' => 'demo',
            'name' => 'Task Lead',
            'email' => 'task@example.test',
            'status' => LeadRequest::STATUS_NEW,
        ]);

        return SalesTask::create([
            'lead_request_id' => $lead->id,
            'title' => 'Call lead',
            'status' => SalesTask::STATUS_OPEN,
            'priority' => 'normal',
            'due_at' => now()->addDay(),
        ]);
    }

    private function superAdmin(): User
    {
        $user = User::factory()->create();
        $user->assignRole('super_admin');

        return $user;
    }

    private function configureMarketing(): void
    {
        config([
            'marketing.enabled' => true,
            'sanfaani.deployment.mode' => 'saas',
            'sanfaani.deployment.license_mode' => 'subscription',
            'features.features.marketing_automation.enabled' => true,
        ]);
    }
}
