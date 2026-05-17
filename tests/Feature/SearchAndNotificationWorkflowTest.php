<?php

namespace Tests\Feature;

use App\Models\School;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\User;
use App\Models\UserSchoolRole;
use App\Notifications\SystemDatabaseNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class SearchAndNotificationWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (['super_admin', 'school_admin'] as $role) {
            Role::findOrCreate($role);
        }
    }

    public function test_school_search_returns_only_current_school_records(): void
    {
        $school = $this->school('Search Primary');
        $otherSchool = $this->school('Other School');
        $class = SchoolClass::create(['school_id' => $school->id, 'name' => 'Basic 4', 'status' => 'active']);
        $otherClass = SchoolClass::create(['school_id' => $otherSchool->id, 'name' => 'Basic 4', 'status' => 'active']);
        $admin = $this->schoolAdmin($school);

        Student::create([
            'school_id' => $school->id,
            'school_class_id' => $class->id,
            'admission_number' => 'ADM-001',
            'first_name' => 'Amina',
            'last_name' => 'Saidu',
            'guardian_email' => 'guardian@example.test',
        ]);
        Student::create([
            'school_id' => $otherSchool->id,
            'school_class_id' => $otherClass->id,
            'admission_number' => 'ADM-002',
            'first_name' => 'Amina',
            'last_name' => 'Outside',
        ]);

        $this->actingAs($admin);
        session(['active_school_id' => $school->id, 'active_role_context' => 'school_admin']);

        $response = $this->getJson(route('search', ['q' => 'Amina']));

        $response->assertOk()
            ->assertJsonFragment(['title' => 'Amina Saidu'])
            ->assertJsonMissing(['title' => 'Amina Outside']);
    }

    public function test_notification_feed_is_database_backed_and_marks_read(): void
    {
        $user = User::factory()->create();

        $user->notify(new SystemDatabaseNotification([
            'title' => 'Result published',
            'body' => 'A result was published.',
            'category' => 'results',
            'event' => 'result.published',
            'action_url' => '/notifications',
        ]));

        $notification = $user->notifications()->firstOrFail();

        $this->actingAs($user)
            ->getJson(route('notifications.feed'))
            ->assertOk()
            ->assertJsonPath('unread_count', 1)
            ->assertJsonFragment(['title' => 'Result published']);

        $this->actingAs($user)
            ->post(route('notifications.read', $notification->id), ['redirect' => '/notifications'])
            ->assertRedirect('/notifications');

        $this->assertSame(0, $user->fresh()->unreadNotifications()->count());
    }

    private function school(string $name): School
    {
        return School::create([
            'name' => $name,
            'slug' => fake()->unique()->slug(),
            'status' => 'active',
            'subscription_status' => 'active',
        ]);
    }

    private function schoolAdmin(School $school): User
    {
        $user = User::factory()->create(['school_id' => $school->id]);
        $user->assignRole('school_admin');

        UserSchoolRole::create([
            'user_id' => $user->id,
            'school_id' => $school->id,
            'role_name' => 'school_admin',
            'status' => 'active',
        ]);

        return $user;
    }
}
