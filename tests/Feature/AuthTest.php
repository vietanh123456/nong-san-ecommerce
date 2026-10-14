<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register(): void
    {
        $response = $this->post(route('register.store'), [
            'name' => 'Nguyen Van A',
            'email' => 'a@example.com',
            'phone' => '0987654321',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect(route('login'));

        $this->assertGuest();

        $this->assertDatabaseHas('users', [
            'name' => 'Nguyen Van A',
            'email' => 'a@example.com',
            'phone' => '0987654321',
            'role' => 'buyer',
        ]);
    }

    public function test_user_can_login_with_valid_credentials(): void
    {
        $user = User::factory()->create([
            'email' => 'a@example.com',
            'password' => 'password123',
        ]);

        $response = $this->post(route('login.store'), [
            'email' => 'a@example.com',
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('home'));

        $this->assertAuthenticatedAs($user);
    }

    public function test_login_rejects_invalid_credentials(): void
    {
        User::factory()->create([
            'email' => 'a@example.com',
            'password' => 'password123',
        ]);

        $response = $this
            ->from(route('login'))
            ->post(route('login.store'), [
                'email' => 'a@example.com',
                'password' => 'wrong-password',
            ]);

        $response->assertRedirect(route('login'));
        $response->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_login_is_locked_for_sixty_seconds_after_five_failed_attempts(): void
    {
        $user = User::factory()->create([
            'email' => 'a@example.com',
            'password' => 'password123',
        ]);

        foreach (range(1, 5) as $attempt) {
            $response = $this
                ->from(route('login'))
                ->post(route('login.store'), [
                    'email' => 'a@example.com',
                    'password' => 'wrong-password',
                ]);

            $response
                ->assertRedirect(route('login'))
                ->assertSessionHasErrors('email');
        }

        $this
            ->from(route('login'))
            ->post(route('login.store'), [
                'email' => 'a@example.com',
                'password' => 'password123',
            ])
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('email');

        $this->assertGuest();
        $this->assertGreaterThan(
            0,
            RateLimiter::availableIn('a@example.com|127.0.0.1')
        );
        $this->assertLessThanOrEqual(
            60,
            RateLimiter::availableIn('a@example.com|127.0.0.1')
        );

        $this->travel(61)->seconds();

        $this
            ->post(route('login.store'), [
                'email' => 'a@example.com',
                'password' => 'password123',
            ])
            ->assertRedirect(route('home'));

        $this->assertAuthenticatedAs($user);
    }

    public function test_authenticated_user_can_logout(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->post(route('logout'));

        $response->assertRedirect(route('login'));

        $this->assertGuest();
    }
}
