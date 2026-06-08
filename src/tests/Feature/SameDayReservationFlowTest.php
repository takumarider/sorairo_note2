<?php

namespace Tests\Feature;

use App\Models\BusinessHour;
use App\Models\Menu;
use App\Models\ReservationPublicationMonth;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SameDayReservationFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        for ($day = 0; $day < 7; $day++) {
            BusinessHour::create([
                'day_of_week' => $day,
                'open_time' => '10:00',
                'close_time' => '20:00',
                'is_closed' => false,
            ]);
        }

        ReservationPublicationMonth::updateOrCreate([
            'year_month' => now('Asia/Tokyo')->format('Y-m'),
        ], [
            'is_published' => true,
        ]);
    }

    public function test_same_day_times_page_requires_authentication(): void
    {
        $response = $this->get(route('reservations.same-day.times'));

        $response->assertRedirect(route('login'));
    }

    public function test_same_day_times_page_shows_available_time_buttons(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 6, 8, 9, 0, 0, 'Asia/Tokyo'));

        try {
            ReservationPublicationMonth::updateOrCreate([
                'year_month' => '2026-06',
            ], [
                'is_published' => true,
            ]);

            Menu::factory()->create([
                'is_event' => false,
                'is_active' => true,
                'duration' => 60,
            ]);

            $user = User::factory()->create();

            $response = $this->actingAs($user)->get(route('reservations.same-day.times'));

            $response->assertOk();
            $response->assertSee('今日の予約');
            $response->assertSee('value="10:00"', false);
        } finally {
            Carbon::setTestNow();
        }
    }

    public function test_same_day_menus_filters_unavailable_long_duration_menu(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 6, 8, 9, 0, 0, 'Asia/Tokyo'));

        try {
            ReservationPublicationMonth::updateOrCreate([
                'year_month' => '2026-06',
            ], [
                'is_published' => true,
            ]);

            BusinessHour::query()->delete();
            for ($day = 0; $day < 7; $day++) {
                BusinessHour::create([
                    'day_of_week' => $day,
                    'open_time' => '10:00',
                    'close_time' => '11:00',
                    'is_closed' => false,
                ]);
            }

            $shortMenu = Menu::factory()->create([
                'name' => 'ショートメニュー',
                'is_event' => false,
                'is_active' => true,
                'duration' => 30,
            ]);

            $longMenu = Menu::factory()->create([
                'name' => 'ロングメニュー',
                'is_event' => false,
                'is_active' => true,
                'duration' => 90,
            ]);

            $user = User::factory()->create();

            $response = $this->actingAs($user)->get(route('reservations.same-day.menus', [
                'start_time' => '10:30',
            ]));

            $response->assertOk();
            $response->assertSee($shortMenu->name);
            $response->assertDontSee($longMenu->name);
        } finally {
            Carbon::setTestNow();
        }
    }

    public function test_same_day_menus_rejects_past_time_selection(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 6, 8, 10, 10, 0, 'Asia/Tokyo'));

        try {
            ReservationPublicationMonth::updateOrCreate([
                'year_month' => '2026-06',
            ], [
                'is_published' => true,
            ]);

            Menu::factory()->create([
                'is_event' => false,
                'is_active' => true,
                'duration' => 60,
            ]);

            $user = User::factory()->create();

            $response = $this->actingAs($user)->get(route('reservations.same-day.menus', [
                'start_time' => '10:00',
            ]));

            $response->assertRedirect(route('reservations.same-day.times'));
            $response->assertSessionHasErrors('start_time');
        } finally {
            Carbon::setTestNow();
        }
    }
}
