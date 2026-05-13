<?php

namespace Tests\Feature\Auth;

use App\Events\PasswordResetEmailRequested;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_reset_password_link_screen_can_be_rendered(): void
    {
        $response = $this->get('/forgot-password');

        $response->assertStatus(200);
    }

    public function test_reset_password_link_can_be_requested(): void
    {
        $user = User::factory()->create();
        Event::fake([PasswordResetEmailRequested::class]);

        $this->post('/forgot-password', ['email' => $user->email]);

        Event::assertDispatched(
            PasswordResetEmailRequested::class,
            fn (PasswordResetEmailRequested $event) => $event->user->is($user)
                && filled($event->token)
        );
    }

    public function test_reset_password_screen_can_be_rendered(): void
    {
        $user = User::factory()->create();
        Event::fake([PasswordResetEmailRequested::class]);

        $this->post('/forgot-password', ['email' => $user->email]);

        Event::assertDispatched(PasswordResetEmailRequested::class, function (PasswordResetEmailRequested $event) {
            $response = $this->get('/reset-password/'.$event->token);

            $response->assertStatus(200);

            return true;
        });
    }

    public function test_password_can_be_reset_with_valid_token(): void
    {
        $user = User::factory()->create();
        Event::fake([PasswordResetEmailRequested::class]);

        $this->post('/forgot-password', ['email' => $user->email]);

        Event::assertDispatched(PasswordResetEmailRequested::class, function (PasswordResetEmailRequested $event) use ($user) {
            $response = $this->post('/reset-password', [
                'token' => $event->token,
                'email' => $user->email,
                'password' => 'password',
                'password_confirmation' => 'password',
            ]);

            $response
                ->assertSessionHasNoErrors()
                ->assertRedirect(route('login'));

            return true;
        });
    }
}
