<?php

namespace Tests\Feature\Auth;

use App\Models\Menu;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
    }

    public function test_new_users_can_register(): void
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'Str0ng!Pass#2026',
            'password_confirmation' => 'Str0ng!Pass#2026',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard', absolute: false));
    }

    public function test_new_users_are_redirected_to_intended_reservation_route_after_register(): void
    {
        $menu = Menu::factory()->create();

        $this->get(route('reservations.start', [
            'menu_id' => $menu->id,
        ]))->assertRedirect(route('login'));

        $response = $this->post('/register', [
            'name' => 'Intended User',
            'email' => 'intended@example.com',
            'password' => 'Str0ng!Pass#2026',
            'password_confirmation' => 'Str0ng!Pass#2026',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('reservations.calendar', [
            'menu_id' => $menu->id,
        ], false));
    }
}
