<?php

namespace Tests\Feature;

use App\Models\Menu;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminReservationCalendarTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_open_reservation_calendar_page(): void
    {
        $admin = User::factory()->create([
            'is_admin' => true,
        ]);

        $response = $this->actingAs($admin)->get('/admin/reservations/calendar');

        $response->assertOk();
        $response->assertSee('予約状況カレンダー');
        $response->assertSee('日・週・月で予約を確認');
        $response->assertSee('reservation-calendar-detail', false);
    }

    public function test_reservation_list_shows_calendar_navigation_for_admin(): void
    {
        $admin = User::factory()->create([
            'is_admin' => true,
        ]);

        $response = $this->actingAs($admin)->get('/admin/reservations');

        $response->assertOk();
        $response->assertSee('カレンダーで確認');
        $response->assertSee('/admin/reservations/calendar', false);
    }

    public function test_reservation_list_separates_active_and_ended_tabs(): void
    {
        $admin = User::factory()->create([
            'is_admin' => true,
        ]);
        $customer = User::factory()->create();

        $futureMenu = Menu::factory()->create([
            'name' => '未来メニュー',
        ]);
        $pastMenu = Menu::factory()->create([
            'name' => '過去メニュー',
        ]);

        $futureReservation = Reservation::create([
            'user_id' => $customer->id,
            'menu_id' => $futureMenu->id,
            'slot_id' => null,
            'date' => now('Asia/Tokyo')->addDays(2)->toDateString(),
            'start_time' => '10:00',
            'end_time' => '11:00',
            'status' => 'confirmed',
        ]);

        $pastReservation = Reservation::create([
            'user_id' => $customer->id,
            'menu_id' => $pastMenu->id,
            'slot_id' => null,
            'date' => now('Asia/Tokyo')->subDays(2)->toDateString(),
            'start_time' => '10:00',
            'end_time' => '11:00',
            'status' => 'completed',
        ]);

        $response = $this->actingAs($admin)->get('/admin/reservations');

        $response->assertOk();
        $response->assertSee('予約中');
        $response->assertSee('終了');
        $response->assertSee('未来メニュー');
        $response->assertSee('/admin/reservations/'.$futureReservation->id.'/edit', false);
        $response->assertDontSee('/admin/reservations/'.$pastReservation->id.'/edit', false);

        $endedResponse = $this->actingAs($admin)->get('/admin/reservations?activeTab=ended');

        $endedResponse->assertOk();
        $endedResponse->assertSee('/admin/reservations/'.$pastReservation->id.'/edit', false);
        $endedResponse->assertDontSee('/admin/reservations/'.$futureReservation->id.'/edit', false);
    }
}
