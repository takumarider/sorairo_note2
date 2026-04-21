<?php

namespace Tests\Feature;

use App\Models\Menu;
use App\Models\Reservation;
use App\Models\SystemSetting;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ReservationCancellationPolicyTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_cannot_cancel_after_deadline_hours(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 4, 11, 10, 0, 0, 'Asia/Tokyo'));

        $user = User::factory()->create();
        $menu = Menu::factory()->create();

        SystemSetting::getSingleton()->update([
            'user_cancel_deadline_hours' => 24,
            'welcome_contact_number' => '03-1234-5678',
        ]);

        $reservation = Reservation::create([
            'user_id' => $user->id,
            'menu_id' => $menu->id,
            'date' => '2026-04-12',
            'start_time' => '09:00',
            'end_time' => '10:00',
            'status' => 'confirmed',
        ]);

        $response = $this->actingAs($user)
            ->deleteJson('/api/reservations/'.$reservation->id);

        $response->assertStatus(422);
        $response->assertJsonPath('success', false);
        $response->assertJsonPath('message', 'キャンセル期限を過ぎたため、サロンまで直接ご連絡ください。詳しくはWelcomeページのお問い合わせ番号（03-1234-5678）をご確認ください。');
        $this->assertSame('confirmed', $reservation->fresh()->status);

        Carbon::setTestNow();
    }

    public function test_admin_can_cancel_even_after_user_deadline(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 4, 11, 10, 0, 0, 'Asia/Tokyo'));

        Mail::fake();

        $admin = User::factory()->create(['is_admin' => true]);
        $customer = User::factory()->create();
        $menu = Menu::factory()->create();

        SystemSetting::getSingleton()->update([
            'user_cancel_deadline_hours' => 72,
        ]);

        $reservation = Reservation::create([
            'user_id' => $customer->id,
            'menu_id' => $menu->id,
            'date' => '2026-04-11',
            'start_time' => '11:00',
            'end_time' => '12:00',
            'status' => 'confirmed',
        ]);

        $response = $this->actingAs($admin)
            ->deleteJson('/api/reservations/'.$reservation->id);

        $response->assertOk();
        $response->assertJsonPath('success', true);
        $this->assertSame('canceled', $reservation->fresh()->status);

        Carbon::setTestNow();
    }

    public function test_events_api_returns_only_confirmed_reservations_for_user(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 4, 19, 10, 0, 0, 'Asia/Tokyo'));

        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $menu = Menu::factory()->create(['name' => 'カット', 'price' => 5000]);

        Reservation::create([
            'user_id' => $user->id,
            'menu_id' => $menu->id,
            'date' => '2026-04-20',
            'start_time' => '10:00',
            'end_time' => '11:00',
            'status' => 'confirmed',
        ]);

        Reservation::create([
            'user_id' => $user->id,
            'menu_id' => $menu->id,
            'date' => '2026-04-21',
            'start_time' => '12:00',
            'end_time' => '13:00',
            'status' => 'canceled',
        ]);

        Reservation::create([
            'user_id' => $otherUser->id,
            'menu_id' => $menu->id,
            'date' => '2026-04-22',
            'start_time' => '14:00',
            'end_time' => '15:00',
            'status' => 'confirmed',
        ]);

        $response = $this->actingAs($user)->getJson('/api/reservations/events');

        $response->assertOk();
        $response->assertJsonPath('success', true);
        $response->assertJsonCount(1, 'reservations');
        $response->assertJsonPath('reservations.0.menu_name', 'カット');

        Carbon::setTestNow();
    }
}
