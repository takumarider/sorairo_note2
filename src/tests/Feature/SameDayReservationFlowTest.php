<?php

namespace Tests\Feature;

use App\Models\BusinessHour;
use App\Models\Menu;
use App\Models\Reservation;
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

            $response = $this->actingAs($user)->get(route('reservations.same-day.times'));

            $response->assertOk();
            $response->assertSee('今日予約する');
            $response->assertDontSee('value="10:30"', false);
            $response->assertSee('value="11:00"', false);
            $this->assertSame(3, substr_count($response->getContent(), 'inline-flex shrink-0 whitespace-nowrap rounded-full'));
            $response->assertSeeText('受付中');
        } finally {
            Carbon::setTestNow();
        }
    }

    public function test_same_day_navigation_shows_preparing_when_no_times_are_available(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 6, 8, 19, 30, 0, 'Asia/Tokyo'));

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
            $response->assertDontSee('value="19:00"', false);
            $this->assertSame(3, substr_count($response->getContent(), 'inline-flex shrink-0 whitespace-nowrap rounded-full'));
            $response->assertSeeText('準備中');
        } finally {
            Carbon::setTestNow();
        }
    }

    public function test_same_day_navigation_shows_preparing_when_all_times_are_booked(): void
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

            $menu = Menu::factory()->create([
                'is_event' => false,
                'is_active' => true,
                'duration' => 30,
            ]);
            $user = User::factory()->create();

            foreach ([['10:00', '10:30'], ['10:30', '11:00']] as [$startTime, $endTime]) {
                Reservation::create([
                    'user_id' => $user->id,
                    'menu_id' => $menu->id,
                    'slot_id' => null,
                    'date' => '2026-06-08',
                    'start_time' => $startTime,
                    'end_time' => $endTime,
                    'status' => 'confirmed',
                ]);
            }

            $response = $this->actingAs($user)->get(route('reservations.same-day.times'));

            $response->assertOk();
            $response->assertSee('本日ご案内できる時間がありません。');
            $this->assertSame(3, substr_count($response->getContent(), 'inline-flex shrink-0 whitespace-nowrap rounded-full'));
            $response->assertSeeText('準備中');
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

    public function test_same_day_menus_rejects_time_within_thirty_minutes(): void
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
                'start_time' => '10:30',
            ]));

            $response->assertRedirect(route('reservations.same-day.times'));
            $response->assertSessionHasErrors('start_time');
        } finally {
            Carbon::setTestNow();
        }
    }

    public function test_same_day_store_redirects_to_time_selection_and_shows_overlap_error(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 6, 8, 9, 0, 0, 'Asia/Tokyo'));

        try {
            ReservationPublicationMonth::updateOrCreate([
                'year_month' => '2026-06',
            ], [
                'is_published' => true,
            ]);

            $reservedMenu = Menu::factory()->create([
                'is_event' => false,
                'is_active' => true,
                'duration' => 60,
            ]);
            $menu = Menu::factory()->create([
                'is_event' => false,
                'is_active' => true,
                'duration' => 60,
            ]);
            $reservedBy = User::factory()->create();
            $user = User::factory()->create();

            Reservation::create([
                'user_id' => $reservedBy->id,
                'menu_id' => $reservedMenu->id,
                'slot_id' => null,
                'date' => '2026-06-08',
                'start_time' => '10:00',
                'end_time' => '11:00',
                'status' => 'confirmed',
            ]);

            $response = $this->actingAs($user)->post(route('reservations.store'), [
                'menu_id' => $menu->id,
                'date' => '2026-06-08',
                'start_time' => '10:30',
                'options' => [],
                'reservation_flow' => 'same_day',
            ]);

            $message = '選択した時間では予約できません。予約状況や営業時間が変わった可能性があります。時間またはメニュー・オプションを選び直してください。';
            $response->assertRedirect(route('reservations.same-day.times'));
            $response->assertSessionHasErrors([
                'start_time' => $message,
            ]);
            $this->assertDatabaseCount('reservations', 1);

            $timesResponse = $this->actingAs($user)->get(route('reservations.same-day.times'));
            $timesResponse->assertOk();
            $timesResponse->assertSee('role="alert"', false);
            $timesResponse->assertSee($message);
        } finally {
            Carbon::setTestNow();
        }
    }

    public function test_same_day_flow_pages_include_loading_overlay_messages(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 6, 8, 9, 0, 0, 'Asia/Tokyo'));

        try {
            ReservationPublicationMonth::updateOrCreate([
                'year_month' => '2026-06',
            ], [
                'is_published' => true,
            ]);

            Menu::factory()->create([
                'name' => 'ベーシックメニュー',
                'is_event' => false,
                'is_active' => true,
                'duration' => 60,
            ]);

            $user = User::factory()->create();

            $timesResponse = $this->actingAs($user)->get(route('reservations.same-day.times'));
            $timesResponse->assertOk();
            $timesResponse->assertSee('data-loading-message="予約可能なメニューを確認しています。しばらくお待ちください。"', false);

            $menusResponse = $this->actingAs($user)->get(route('reservations.same-day.menus', [
                'start_time' => '10:00',
            ]));
            $menusResponse->assertOk();
            $menusResponse->assertSee('data-loading-message="予約内容を確認しています。しばらくお待ちください。"', false);
            $menusResponse->assertSee('name="reservation_flow" value="same_day"', false);

            $confirmResponse = $this->actingAs($user)->get(route('reservations.confirm', [
                'menu_id' => Menu::query()->firstOrFail()->id,
                'date' => '2026-06-08',
                'start_time' => '10:00',
                'reservation_flow' => 'same_day',
            ]));
            $confirmResponse->assertOk();
            $confirmResponse->assertSee('name="reservation_flow" value="same_day"', false);
        } finally {
            Carbon::setTestNow();
        }
    }
}
