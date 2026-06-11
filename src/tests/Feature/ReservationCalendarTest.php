<?php

namespace Tests\Feature;

use App\Models\BusinessHour;
use App\Models\Menu;
use App\Models\ReservationPublicationMonth;
use App\Models\User;
use Carbon\Carbon;
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

        ReservationPublicationMonth::create([
            'year_month' => now('Asia/Tokyo')->startOfMonth()->addMonth()->format('Y-m'),
            'is_published' => true,
        ]);
    }

    public function test_menu_show_contains_hidden_menu_id_and_calendar_form_action(): void
    {
        $menu = Menu::factory()->create();
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('menus.show', ['menu' => $menu->id]));

        $response->assertOk();
        $response->assertSee('action="'.route('reservations.start').'"', false);
        $response->assertSee('name="menu_id" value="'.$menu->id.'"', false);
    }

    public function test_guest_can_browse_menu_pages(): void
    {
        $menu = Menu::factory()->create();

        $this->get(route('menus.index'))->assertOk();
        $this->get(route('menus.show', ['menu' => $menu->id]))->assertOk();
    }

    public function test_guest_is_redirected_to_login_when_opening_calendar(): void
    {
        $menu = Menu::factory()->create();

        $response = $this->get(route('reservations.calendar', [
            'menu_id' => $menu->id,
        ]));

        $response->assertRedirect(route('login'));
    }

    public function test_guest_is_redirected_to_login_from_start_route_with_message_and_intended(): void
    {
        $menu = Menu::factory()->create();

        $response = $this->get(route('reservations.start', [
            'menu_id' => $menu->id,
        ]));

        $response->assertRedirect(route('login'));
        $response->assertSessionHas('status', '予約するためには「新規登録」が必要です。');
        $response->assertSessionHas('url.intended', route('reservations.calendar', [
            'menu_id' => $menu->id,
        ], false));
    }

    public function test_authenticated_user_is_redirected_to_calendar_from_start_route(): void
    {
        $menu = Menu::factory()->create();
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('reservations.start', [
            'menu_id' => $menu->id,
        ]));

        $response->assertRedirect(route('reservations.calendar', [
            'menu_id' => $menu->id,
        ]));
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
        $targetDate = now('Asia/Tokyo')->addDay();

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
            'year_month' => now('Asia/Tokyo')->startOfMonth()->addMonth()->format('Y-m'),
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
            'year_month' => now('Asia/Tokyo')->startOfMonth()->addMonth()->format('Y-m'),
        ], [
            'is_published' => false,
        ]);

        $menu = Menu::factory()->create();
        $user = User::factory()->create();
        $nextMonth = now('Asia/Tokyo')->startOfMonth()->addMonth()->format('Y-m');

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
            'year_month' => now('Asia/Tokyo')->startOfMonth()->addMonth()->format('Y-m'),
        ], [
            'is_published' => false,
        ]);

        $menu = Menu::factory()->create();
        $user = User::factory()->create();
        $nextMonthDate = now('Asia/Tokyo')->startOfMonth()->addMonth()->addDays(1)->toDateString();

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
            'year_month' => now('Asia/Tokyo')->startOfMonth()->addMonth()->format('Y-m'),
        ], [
            'is_published' => true,
        ]);

        $menu = Menu::factory()->create();
        $user = User::factory()->create();
        $nextMonth = now('Asia/Tokyo')->startOfMonth()->addMonth();

        $response = $this->actingAs($user)->get(route('reservations.calendar', [
            'menu_id' => $menu->id,
            'month' => $nextMonth->format('Y-m'),
        ]));

        $response->assertOk();
        $response->assertSee($nextMonth->isoFormat('Y年M月'));
    }

    public function test_calendar_month_parsing_does_not_skip_june_on_month_end(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 5, 31, 12, 0, 0, 'Asia/Tokyo'));

        ReservationPublicationMonth::updateOrCreate([
            'year_month' => '2026-06',
        ], [
            'is_published' => true,
        ]);

        $menu = Menu::factory()->create();
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('reservations.calendar', [
            'menu_id' => $menu->id,
            'month' => '2026-06',
        ]));

        $response->assertOk();
        $response->assertSee('2026年6月');
        $response->assertDontSee('2026年7月');

        Carbon::setTestNow();
    }

    public function test_calendar_includes_today_when_available(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 6, 8, 9, 0, 0, 'Asia/Tokyo'));

        try {
            ReservationPublicationMonth::updateOrCreate([
                'year_month' => '2026-06',
            ], [
                'is_published' => true,
            ]);

            $menu = Menu::factory()->create();
            $user = User::factory()->create();

            $response = $this->actingAs($user)->get(route('reservations.calendar', [
                'menu_id' => $menu->id,
                'month' => '2026-06',
            ]));

            $response->assertOk();
            $response->assertSee('value="2026-06-08"', false);
        } finally {
            Carbon::setTestNow();
        }
    }
}
