<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class MultiFieldLoginTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a test user with both email and staff_code
        User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'staff_code' => 'STAFF001',
            'password' => Hash::make('password123'),
            'email_verified_at' => now(),
        ]);
    }

    public function test_user_can_login_with_email(): void
    {
        $response = $this->post('/login', [
            'login' => 'test@example.com',
            'password' => 'password123',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard', absolute: false));
    }

    public function test_user_can_login_with_email_case_insensitively(): void
    {
        $response = $this->post('/login', [
            'login' => 'TEST@EXAMPLE.COM',
            'password' => 'password123',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard', absolute: false));
    }

    public function test_user_can_login_with_staff_code_lowercase(): void
    {
        $response = $this->post('/login', [
            'login' => 'staff001',
            'password' => 'password123',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard', absolute: false));
    }

    public function test_user_can_login_with_staff_code_uppercase(): void
    {
        $response = $this->post('/login', [
            'login' => 'STAFF001',
            'password' => 'password123',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard', absolute: false));
    }

    public function test_non_at_identifier_falls_back_to_email(): void
    {
        User::create([
            'name' => 'Legacy User',
            'email' => 'legacy-login',
            'password' => Hash::make('password123'),
            'email_verified_at' => now(),
        ]);

        $response = $this->post('/login', [
            'login' => 'legacy-login',
            'password' => 'password123',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard', absolute: false));
    }

    public function test_super_admin_can_login_with_email_without_staff_code(): void
    {
        $superAdmin = User::create([
            'name' => 'Super Admin',
            'email' => 'admin@example.com',
            'password' => Hash::make('password123'),
            'email_verified_at' => now(),
        ]);

        Role::findOrCreate('super_admin');
        $superAdmin->assignRole('super_admin');

        $response = $this->post('/login', [
            'login' => 'ADMIN@EXAMPLE.COM',
            'password' => 'password123',
        ]);

        $this->assertAuthenticatedAs($superAdmin);
        $response->assertRedirect(route('admin.dashboard'));
    }

    public function test_super_admin_cannot_login_with_staff_code(): void
    {
        $superAdmin = User::create([
            'name' => 'Legacy Super Admin',
            'email' => 'legacy-admin@example.com',
            'staff_code' => 'ADMIN001',
            'password' => Hash::make('password123'),
            'email_verified_at' => now(),
        ]);

        Role::findOrCreate('super_admin');
        $superAdmin->assignRole('super_admin');

        $response = $this->post('/login', [
            'login' => 'ADMIN001',
            'password' => 'password123',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('login');
    }

    public function test_user_cannot_login_with_invalid_email(): void
    {
        $response = $this->post('/login', [
            'login' => 'wrong@example.com',
            'password' => 'password123',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('login');
    }

    public function test_user_cannot_login_with_invalid_staff_code(): void
    {
        $response = $this->post('/login', [
            'login' => 'WRONG001',
            'password' => 'password123',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('login');
    }

    public function test_user_cannot_login_with_wrong_password(): void
    {
        $response = $this->post('/login', [
            'login' => 'test@example.com',
            'password' => 'wrongpassword',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('login');
    }

    public function test_login_field_is_required(): void
    {
        $response = $this->post('/login', [
            'login' => '',
            'password' => 'password123',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('login');
    }
}
