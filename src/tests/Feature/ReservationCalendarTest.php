<?php

namespace Tests\Feature;

use App\Models\BusinessHour;
use App\Models\Menu;
use App\Models\ReservationPublicationMonth;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReservationCalendarTest extends TestCase
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

        ReservationPublicationMonth::create([
            'year_month' => now('Asia/Tokyo')->format('Y-m'),
            'is_published' => true,
        ]);
    }

    public function test_menu_show_contains_hidden_menu_id_and_calendar_form_action(): void
    {
        $menu = Menu::factory()->create();
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('menus.show', ['menu' => $menu->id]));

        $response->assertOk();
        $response->assertSee('action="'.route('reservations.calendar').'"', false);
        $response->assertSee('name="menu_id" value="'.$menu->id.'"', false);
    }

    public function test_user_can_open_calendar_with_menu_id(): void
    {
        $menu = Menu::factory()->create([
            'name' => 'カット',
        ]);
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('reservations.calendar', [
            'menu_id' => $menu->id,
        ]));

        $response->assertOk();
        $response->assertSee('カット');
    }

    public function test_calendar_shows_notice_when_month_has_no_business_hours(): void
    {
        BusinessHour::query()->delete();

        $menu = Menu::factory()->create();
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('reservations.calendar', [
            'menu_id' => $menu->id,
            'month' => now()->format('Y-m'),
        ]));

        $response->assertOk();
        $response->assertSee('この月は営業時間がまだ設定されていません');
    }

    public function test_times_page_shows_closed_day_message(): void
    {
        $targetDate = now()->addDay();

        BusinessHour::updateOrCreate(
            [
                'day_of_week' => $targetDate->dayOfWeek,
                'specific_date' => null,
            ],
            [
                'open_time' => '10:00',
                'close_time' => '20:00',
                'is_closed' => true,
            ],
        );

        $menu = Menu::factory()->create();
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('reservations.times', [
            'menu_id' => $menu->id,
            'date' => $targetDate->toDateString(),
        ]));

        $response->assertOk();
        $response->assertViewHas('availabilityReason', 'closed');
    }

    public function test_current_month_hides_next_month_link_when_unpublished(): void
    {
        ReservationPublicationMonth::updateOrCreate([
            'year_month' => now('Asia/Tokyo')->addMonth()->format('Y-m'),
        ], [
            'is_published' => false,
        ]);

        $menu = Menu::factory()->create();
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('reservations.calendar', [
            'menu_id' => $menu->id,
            'month' => now('Asia/Tokyo')->format('Y-m'),
        ]));

        $response->assertOk();
        $response->assertSee('次月は現在公開されていません');
    }

    public function test_next_month_calendar_redirects_when_unpublished(): void
    {
        ReservationPublicationMonth::updateOrCreate([
            'year_month' => now('Asia/Tokyo')->addMonth()->format('Y-m'),
        ], [
            'is_published' => false,
        ]);

        $menu = Menu::factory()->create();
        $user = User::factory()->create();
        $nextMonth = now('Asia/Tokyo')->addMonth()->format('Y-m');

        $response = $this->actingAs($user)->get(route('reservations.calendar', [
            'menu_id' => $menu->id,
            'month' => $nextMonth,
        ]));

        $response->assertRedirect(route('reservations.calendar', [
            'menu_id' => $menu->id,
            'month' => now('Asia/Tokyo')->format('Y-m'),
        ]));
        $response->assertSessionHas('availability_reason', 'month_unpublished');
    }

    public function test_times_redirects_when_next_month_is_unpublished(): void
    {
        ReservationPublicationMonth::updateOrCreate([
            'year_month' => now('Asia/Tokyo')->addMonth()->format('Y-m'),
        ], [
            'is_published' => false,
        ]);

        $menu = Menu::factory()->create();
        $user = User::factory()->create();
        $nextMonthDate = now('Asia/Tokyo')->addMonth()->startOfMonth()->addDays(1)->toDateString();

        $response = $this->actingAs($user)->get(route('reservations.times', [
            'menu_id' => $menu->id,
            'date' => $nextMonthDate,
        ]));

        $response->assertRedirect(route('reservations.calendar', [
            'menu_id' => $menu->id,
            'month' => now('Asia/Tokyo')->format('Y-m'),
        ]));
        $response->assertSessionHas('availability_reason', 'month_unpublished');
    }

    public function test_next_month_calendar_is_available_after_publication(): void
    {
        ReservationPublicationMonth::updateOrCreate([
            'year_month' => now('Asia/Tokyo')->addMonth()->format('Y-m'),
        ], [
            'is_published' => true,
        ]);

        $menu = Menu::factory()->create();
        $user = User::factory()->create();
        $nextMonth = now('Asia/Tokyo')->addMonth();

        $response = $this->actingAs($user)->get(route('reservations.calendar', [
            'menu_id' => $menu->id,
            'month' => $nextMonth->format('Y-m'),
        ]));

        $response->assertOk();
        $response->assertSee($nextMonth->isoFormat('Y年M月'));
    }
}
