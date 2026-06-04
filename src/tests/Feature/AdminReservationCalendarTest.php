<?php

namespace Tests\Feature;

use App\Filament\Resources\ReservationResource\Pages\ManageReservationCalendar;
use App\Models\BusinessHour;
use App\Models\Menu;
use App\Models\MenuOption;
use App\Models\Reservation;
use App\Models\Slot;
use App\Models\TimeBlock;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
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
        $response->assertSee('イベント枠');
        $response->assertSee('reservation-calendar-slot-detail', false);
    }

    public function test_calendar_events_include_event_slots_even_without_reservations(): void
    {
        $admin = User::factory()->create([
            'is_admin' => true,
        ]);

        $menu = Menu::factory()->create([
            'name' => '春イベント',
            'is_event' => true,
            'duration' => 0,
        ]);

        $rangeStart = now('Asia/Tokyo')->startOfWeek();
        $rangeEndExclusive = now('Asia/Tokyo')->endOfWeek()->addDay();
        $date = $rangeStart->copy()->addDay()->toDateString();
        $slot = Slot::create([
            'menu_id' => $menu->id,
            'date' => $date,
            'start_time' => '14:00',
            'end_time' => '15:30',
            'capacity' => 3,
            'is_reserved' => false,
        ]);

        $this->actingAs($admin);

        $page = app(ManageReservationCalendar::class);
        $events = $page->getCalendarEvents(
            $rangeStart->toIso8601String(),
            $rangeEndExclusive->toIso8601String(),
        );

        $slotEvent = collect($events)->first(fn (array $event): bool => ($event['id'] ?? null) === 'slot-'.$slot->id);

        $this->assertNotNull($slotEvent);
        $this->assertSame('slot', $slotEvent['extendedProps']['type']);
        $this->assertSame(3, $slotEvent['extendedProps']['capacity']);
        $this->assertSame(0, $slotEvent['extendedProps']['confirmed_count']);
        $this->assertSame(3, $slotEvent['extendedProps']['remaining_capacity']);
        $this->assertStringContainsString('（枠）', $slotEvent['title']);
    }

    public function test_calendar_events_hide_canceled_reservations_and_simplify_reservation_title(): void
    {
        $admin = User::factory()->create([
            'is_admin' => true,
        ]);
        $customer = User::factory()->create([
            'name' => '山田 太郎',
        ]);

        $visibleMenu = Menu::factory()->create([
            'name' => '全身ケア',
        ]);
        $hiddenMenu = Menu::factory()->create([
            'name' => 'キャンセル対象メニュー',
        ]);

        $rangeStart = now('Asia/Tokyo')->startOfWeek();
        $rangeEndExclusive = now('Asia/Tokyo')->endOfWeek()->addDay();
        $date = $rangeStart->copy()->addDay()->toDateString();

        $visibleReservation = Reservation::create([
            'user_id' => $customer->id,
            'menu_id' => $visibleMenu->id,
            'slot_id' => null,
            'date' => $date,
            'start_time' => '10:00',
            'end_time' => '11:00',
            'status' => 'confirmed',
        ]);

        Reservation::create([
            'user_id' => $customer->id,
            'menu_id' => $hiddenMenu->id,
            'slot_id' => null,
            'date' => $date,
            'start_time' => '12:00',
            'end_time' => '13:00',
            'status' => 'canceled',
        ]);

        $this->actingAs($admin);

        $page = app(ManageReservationCalendar::class);
        $events = collect($page->getCalendarEvents(
            $rangeStart->toIso8601String(),
            $rangeEndExclusive->toIso8601String(),
        ));

        $visibleEvent = $events->first(fn (array $event): bool => ($event['id'] ?? null) === (string) $visibleReservation->id);

        $this->assertNotNull($visibleEvent);
        $this->assertSame('山田 太郎 / 全身ケア', $visibleEvent['title']);
        $this->assertSame('reservation', $visibleEvent['extendedProps']['type']);
        $this->assertFalse($events->contains(fn (array $event): bool => ($event['extendedProps']['menuName'] ?? null) === 'キャンセル対象メニュー'));
        $this->assertFalse($events->contains(fn (array $event): bool => ($event['extendedProps']['statusLabel'] ?? null) === 'キャンセル'));
    }

    public function test_admin_can_update_selected_reservation_options_and_end_time_from_calendar_modal(): void
    {
        $admin = User::factory()->create([
            'is_admin' => true,
        ]);
        $customer = User::factory()->create();

        $menu = Menu::factory()->create([
            'is_event' => false,
            'duration' => 60,
            'is_active' => true,
        ]);

        $option = MenuOption::create([
            'menu_id' => $menu->id,
            'name' => 'ヘッドスパ',
            'price' => 2000,
            'duration' => 30,
            'is_active' => true,
        ]);

        $date = now('Asia/Tokyo')->addDays(1)->toDateString();
        $reservation = Reservation::create([
            'user_id' => $customer->id,
            'menu_id' => $menu->id,
            'slot_id' => null,
            'date' => $date,
            'start_time' => '10:00',
            'end_time' => '11:00',
            'status' => 'confirmed',
        ]);

        Livewire::actingAs($admin)
            ->test(ManageReservationCalendar::class)
            ->call('openReservationModal', $reservation->id)
            ->set('selectedReservationOptionIds', [$option->id])
            ->call('updateSelectedReservationOptions')
            ->assertSet('selectedReservation.id', $reservation->id)
            ->assertSet('selectedReservationOptionIds', [$option->id]);

        $reservation->refresh();

        $this->assertSame('10:00', $reservation->start_time->format('H:i'));
        $this->assertSame('11:30', $reservation->end_time->format('H:i'));
        $this->assertDatabaseHas('reservation_options', [
            'reservation_id' => $reservation->id,
            'menu_option_id' => $option->id,
        ]);
    }

    public function test_option_update_is_rejected_when_new_duration_conflicts_with_other_reservation(): void
    {
        $admin = User::factory()->create([
            'is_admin' => true,
        ]);
        $customer = User::factory()->create();

        $menu = Menu::factory()->create([
            'is_event' => false,
            'duration' => 60,
            'is_active' => true,
        ]);

        $conflictingOption = MenuOption::create([
            'menu_id' => $menu->id,
            'name' => 'ロングケア',
            'price' => 3000,
            'duration' => 60,
            'is_active' => true,
        ]);

        $date = now('Asia/Tokyo')->addDays(2)->toDateString();
        $reservation = Reservation::create([
            'user_id' => $customer->id,
            'menu_id' => $menu->id,
            'slot_id' => null,
            'date' => $date,
            'start_time' => '10:00',
            'end_time' => '11:00',
            'status' => 'confirmed',
        ]);

        Reservation::create([
            'user_id' => $customer->id,
            'menu_id' => $menu->id,
            'slot_id' => null,
            'date' => $date,
            'start_time' => '11:00',
            'end_time' => '12:00',
            'status' => 'confirmed',
        ]);

        Livewire::actingAs($admin)
            ->test(ManageReservationCalendar::class)
            ->call('openReservationModal', $reservation->id)
            ->set('selectedReservationOptionIds', [$conflictingOption->id])
            ->call('updateSelectedReservationOptions');

        $reservation->refresh();

        $this->assertSame('11:00', $reservation->end_time->format('H:i'));
        $this->assertDatabaseMissing('reservation_options', [
            'reservation_id' => $reservation->id,
            'menu_option_id' => $conflictingOption->id,
        ]);
    }

    public function test_option_update_is_rejected_for_non_confirmed_reservation(): void
    {
        $admin = User::factory()->create([
            'is_admin' => true,
        ]);
        $customer = User::factory()->create();

        $menu = Menu::factory()->create([
            'is_event' => false,
            'duration' => 60,
            'is_active' => true,
        ]);

        $option = MenuOption::create([
            'menu_id' => $menu->id,
            'name' => '追加ケア',
            'price' => 1500,
            'duration' => 30,
            'is_active' => true,
        ]);

        $reservation = Reservation::create([
            'user_id' => $customer->id,
            'menu_id' => $menu->id,
            'slot_id' => null,
            'date' => now('Asia/Tokyo')->addDays(3)->toDateString(),
            'start_time' => '10:00',
            'end_time' => '11:00',
            'status' => 'completed',
        ]);

        Livewire::actingAs($admin)
            ->test(ManageReservationCalendar::class)
            ->call('openReservationModal', $reservation->id)
            ->set('selectedReservationOptionIds', [$option->id])
            ->call('updateSelectedReservationOptions');

        $reservation->refresh();

        $this->assertSame('11:00', $reservation->end_time->format('H:i'));
        $this->assertDatabaseMissing('reservation_options', [
            'reservation_id' => $reservation->id,
            'menu_option_id' => $option->id,
        ]);
    }

    public function test_admin_can_update_event_menu_reservation_options(): void
    {
        $admin = User::factory()->create([
            'is_admin' => true,
        ]);
        $customer = User::factory()->create();

        $menu = Menu::factory()->create([
            'is_event' => true,
            'duration' => 0,
            'is_active' => true,
        ]);

        $option = MenuOption::create([
            'menu_id' => $menu->id,
            'name' => '撮影オプション',
            'price' => 1000,
            'duration' => 30,
            'is_active' => true,
        ]);

        $date = now('Asia/Tokyo')->addDays(4)->toDateString();
        $slot = Slot::create([
            'menu_id' => $menu->id,
            'date' => $date,
            'start_time' => '14:00',
            'end_time' => '15:00',
            'capacity' => 2,
            'is_reserved' => false,
        ]);

        $reservation = Reservation::create([
            'user_id' => $customer->id,
            'menu_id' => $menu->id,
            'slot_id' => $slot->id,
            'date' => $date,
            'start_time' => '14:00',
            'end_time' => '14:00',
            'status' => 'confirmed',
        ]);

        Livewire::actingAs($admin)
            ->test(ManageReservationCalendar::class)
            ->call('openReservationModal', $reservation->id)
            ->set('selectedReservationOptionIds', [$option->id])
            ->call('updateSelectedReservationOptions');

        $reservation->refresh();

        $this->assertSame('14:30', $reservation->end_time->format('H:i'));
        $this->assertDatabaseHas('reservation_options', [
            'reservation_id' => $reservation->id,
            'menu_option_id' => $option->id,
        ]);
    }

    public function test_calendar_header_summary_includes_business_hours_and_closed_days(): void
    {
        $admin = User::factory()->create([
            'is_admin' => true,
        ]);

        $rangeStart = now('Asia/Tokyo')->startOfWeek();
        $rangeEndExclusive = now('Asia/Tokyo')->endOfWeek()->addDay();

        $openDate = $rangeStart->copy()->addDays(2);
        $closedDate = $rangeStart->copy()->addDays(4);

        $this->createBusinessHour($openDate, '09:30:00', '18:30:00');
        $this->createBusinessHour($closedDate, '10:00:00', '20:00:00', true);

        $this->actingAs($admin);

        $page = app(ManageReservationCalendar::class);
        $events = $page->getCalendarEvents(
            $rangeStart->toIso8601String(),
            $rangeEndExclusive->toIso8601String(),
        );

        $this->assertFalse(collect($events)->contains(fn (array $event): bool => ($event['extendedProps']['type'] ?? null) === 'business-hour'));

        $summary = $page->getCalendarBusinessHourSummary(
            $rangeStart->toIso8601String(),
            $rangeEndExclusive->toIso8601String(),
        );

        $this->assertSame('曜日別設定なし', $summary['regular_label']);
        $this->assertStringContainsString('09:30-18:30', $summary['specific_label']);
        $this->assertStringContainsString('休業', $summary['specific_label']);
    }

    public function test_calendar_business_hour_by_date_returns_status_and_label(): void
    {
        $admin = User::factory()->create([
            'is_admin' => true,
        ]);

        $rangeStart = now('Asia/Tokyo')->startOfWeek();
        $rangeEndExclusive = now('Asia/Tokyo')->endOfWeek()->addDay();

        $openDate = $rangeStart->copy()->addDay();
        $closedDate = $rangeStart->copy()->addDays(2);
        $unsetDate = $rangeStart->copy()->addDays(3);

        BusinessHour::create([
            'day_of_week' => $openDate->dayOfWeek,
            'specific_date' => null,
            'open_time' => '10:00:00',
            'close_time' => '19:00:00',
            'is_closed' => false,
        ]);

        $this->createBusinessHour($closedDate, '10:00:00', '20:00:00', true);

        $this->actingAs($admin);

        $page = app(ManageReservationCalendar::class);
        $byDate = $page->getCalendarBusinessHourByDate(
            $rangeStart->toIso8601String(),
            $rangeEndExclusive->toIso8601String(),
        );

        $this->assertSame('open', $byDate[$openDate->toDateString()]['status']);
        $this->assertSame('10:00-19:00', $byDate[$openDate->toDateString()]['label']);
        $this->assertFalse($byDate[$openDate->toDateString()]['is_specific']);

        $this->assertSame('closed', $byDate[$closedDate->toDateString()]['status']);
        $this->assertSame('休業', $byDate[$closedDate->toDateString()]['label']);
        $this->assertTrue($byDate[$closedDate->toDateString()]['is_specific']);

        $this->assertSame('unset', $byDate[$unsetDate->toDateString()]['status']);
        $this->assertSame('未設定', $byDate[$unsetDate->toDateString()]['label']);
        $this->assertFalse($byDate[$unsetDate->toDateString()]['is_specific']);
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

    public function test_reservation_list_separates_active_ended_and_canceled_tabs(): void
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
        $canceledMenu = Menu::factory()->create([
            'name' => 'キャンセルメニュー',
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

        $canceledReservation = Reservation::create([
            'user_id' => $customer->id,
            'menu_id' => $canceledMenu->id,
            'slot_id' => null,
            'date' => now('Asia/Tokyo')->addDays(1)->toDateString(),
            'start_time' => '14:00',
            'end_time' => '15:00',
            'status' => 'canceled',
        ]);

        $response = $this->actingAs($admin)->get('/admin/reservations');

        $response->assertOk();
        $response->assertSee('予約中');
        $response->assertSee('終了');
        $response->assertSee('キャンセル');
        $response->assertSee('未来メニュー');
        $response->assertSee('/admin/reservations/'.$futureReservation->id.'/edit', false);
        $response->assertDontSee('/admin/reservations/'.$pastReservation->id.'/edit', false);
        $response->assertDontSee('/admin/reservations/'.$canceledReservation->id.'/edit', false);

        $endedResponse = $this->actingAs($admin)->get('/admin/reservations?activeTab=ended');

        $endedResponse->assertOk();
        $endedResponse->assertSee('/admin/reservations/'.$pastReservation->id.'/edit', false);
        $endedResponse->assertDontSee('/admin/reservations/'.$futureReservation->id.'/edit', false);
        $endedResponse->assertDontSee('/admin/reservations/'.$canceledReservation->id.'/edit', false);

        $canceledResponse = $this->actingAs($admin)->get('/admin/reservations?activeTab=canceled');

        $canceledResponse->assertOk();
        $canceledResponse->assertSee('/admin/reservations/'.$canceledReservation->id.'/edit', false);
        $canceledResponse->assertDontSee('/admin/reservations/'.$futureReservation->id.'/edit', false);
        $canceledResponse->assertDontSee('/admin/reservations/'.$pastReservation->id.'/edit', false);
    }

    public function test_admin_can_create_direct_treatment_reservation_from_calendar(): void
    {
        $admin = User::factory()->create([
            'is_admin' => true,
        ]);
        $customer = User::factory()->create();

        $menu = Menu::factory()->create([
            'is_event' => false,
            'duration' => 60,
            'price' => 7000,
            'is_active' => true,
        ]);
        $option = MenuOption::create([
            'menu_id' => $menu->id,
            'name' => 'ヘッドマッサージ',
            'price' => 1000,
            'duration' => 30,
            'is_active' => true,
        ]);

        $date = now('Asia/Tokyo')->addDays(2)->startOfDay();
        $this->createBusinessHour($date, '10:00:00', '20:00:00');

        $start = $date->copy()->setTime(11, 0, 0);
        $end = $date->copy()->setTime(12, 30, 0);

        Livewire::actingAs($admin)
            ->test(ManageReservationCalendar::class)
            ->set('directReservationUserId', $customer->id)
            ->set('directReservationMenuId', $menu->id)
            ->set('directReservationOptionIds', [$option->id])
            ->call('createDirectReservationFromCalendar', $start->toIso8601String(), $end->toIso8601String())
            ->assertSet('directReservationMenuId', $menu->id);

        $reservation = Reservation::query()->where('user_id', $customer->id)->latest('id')->first();

        $this->assertNotNull($reservation);
        $this->assertSame($menu->id, $reservation->menu_id);
        $this->assertNull($reservation->slot_id);
        $this->assertSame($start->toDateString(), $reservation->date->toDateString());
        $this->assertSame('11:00', $reservation->start_time->format('H:i'));
        $this->assertSame('12:30', $reservation->end_time->format('H:i'));
        $this->assertSame('confirmed', $reservation->status);
        $this->assertSame([$option->id], $reservation->options()->pluck('menu_option_id')->all());
    }

    public function test_direct_reservation_is_rejected_when_time_block_conflicts(): void
    {
        $admin = User::factory()->create([
            'is_admin' => true,
        ]);
        $customer = User::factory()->create();

        $menu = Menu::factory()->create([
            'is_event' => false,
            'duration' => 60,
            'is_active' => true,
        ]);

        $date = now('Asia/Tokyo')->addDays(3)->startOfDay();
        $this->createBusinessHour($date, '10:00:00', '20:00:00');

        $start = $date->copy()->setTime(11, 0, 0);
        $end = $date->copy()->setTime(12, 0, 0);

        TimeBlock::create([
            'start_at' => $date->copy()->setTime(10, 30, 0),
            'end_at' => $date->copy()->setTime(12, 0, 0),
            'reason' => '臨時対応',
            'is_active' => true,
        ]);

        Livewire::actingAs($admin)
            ->test(ManageReservationCalendar::class)
            ->set('directReservationUserId', $customer->id)
            ->set('directReservationMenuId', $menu->id)
            ->call('createDirectReservationFromCalendar', $start->toIso8601String(), $end->toIso8601String());

        $this->assertDatabaseMissing('reservations', [
            'user_id' => $customer->id,
            'menu_id' => $menu->id,
            'date' => $date->toDateString(),
            'start_time' => '11:00:00',
            'status' => 'confirmed',
        ]);
    }

    public function test_admin_can_create_direct_event_reservation_and_reject_full_slot(): void
    {
        $admin = User::factory()->create([
            'is_admin' => true,
        ]);
        $customer1 = User::factory()->create();
        $customer2 = User::factory()->create();

        $eventMenu = Menu::factory()->create([
            'is_event' => true,
            'duration' => 0,
            'is_active' => true,
        ]);

        $date = now('Asia/Tokyo')->addDays(4)->startOfDay();
        $slot = Slot::create([
            'menu_id' => $eventMenu->id,
            'date' => $date->toDateString(),
            'start_time' => '14:00',
            'end_time' => '15:00',
            'capacity' => 1,
            'is_reserved' => false,
        ]);

        Livewire::actingAs($admin)
            ->test(ManageReservationCalendar::class)
            ->set('directReservationUserId', $customer1->id)
            ->set('directReservationMenuId', $eventMenu->id)
            ->set('directReservationSlotId', $slot->id)
            ->call(
                'createDirectReservationFromCalendar',
                $date->copy()->setTime(14, 0, 0)->toIso8601String(),
                $date->copy()->setTime(15, 0, 0)->toIso8601String(),
            );

        $this->assertDatabaseHas('reservations', [
            'user_id' => $customer1->id,
            'menu_id' => $eventMenu->id,
            'slot_id' => $slot->id,
            'date' => $date->toDateString(),
            'status' => 'confirmed',
        ]);

        Livewire::actingAs($admin)
            ->test(ManageReservationCalendar::class)
            ->set('directReservationUserId', $customer2->id)
            ->set('directReservationMenuId', $eventMenu->id)
            ->set('directReservationSlotId', $slot->id)
            ->call(
                'createDirectReservationFromCalendar',
                $date->copy()->setTime(14, 0, 0)->toIso8601String(),
                $date->copy()->setTime(15, 0, 0)->toIso8601String(),
            );

        $this->assertSame(1, Reservation::query()->where('slot_id', $slot->id)->where('status', 'confirmed')->count());
    }

    public function test_admin_can_create_direct_treatment_reservation_with_guest_name(): void
    {
        $admin = User::factory()->create([
            'is_admin' => true,
        ]);

        $menu = Menu::factory()->create([
            'is_event' => false,
            'duration' => 60,
            'is_active' => true,
        ]);

        $date = now('Asia/Tokyo')->addDays(5)->startOfDay();
        $this->createBusinessHour($date, '10:00:00', '20:00:00');

        $start = $date->copy()->setTime(13, 0, 0);
        $end = $date->copy()->setTime(14, 0, 0);
        $guestName = '体験予約 花子';

        Livewire::actingAs($admin)
            ->test(ManageReservationCalendar::class)
            ->set('directReservationGuestName', $guestName)
            ->set('directReservationMenuId', $menu->id)
            ->call('createDirectReservationFromCalendar', $start->toIso8601String(), $end->toIso8601String());

        $guestUser = User::query()
            ->where('name', $guestName)
            ->where('email', 'like', User::directReservationGuestEmailLikePattern())
            ->latest('id')
            ->first();

        $this->assertNotNull($guestUser);

        $reservation = Reservation::query()
            ->where('user_id', $guestUser->id)
            ->where('menu_id', $menu->id)
            ->latest('id')
            ->first();

        $this->assertNotNull($reservation);
        $this->assertSame($start->toDateString(), $reservation->date->toDateString());
        $this->assertSame('13:00', $reservation->start_time->format('H:i'));
        $this->assertSame('14:00', $reservation->end_time->format('H:i'));

        $users = Livewire::actingAs($admin)
            ->test(ManageReservationCalendar::class)
            ->instance()
            ->getDirectReservationUsers();

        $this->assertFalse(collect($users)->contains(fn (array $user): bool => (int) $user['id'] === (int) $guestUser->id));
    }

    public function test_admin_can_create_direct_reservation_in_other_mode_with_selected_time_range(): void
    {
        $admin = User::factory()->create([
            'is_admin' => true,
        ]);
        $customer = User::factory()->create();

        $otherMenu = Menu::factory()->create([
            'name' => 'その他',
            'is_event' => false,
            'duration' => 30,
            'is_active' => true,
        ]);

        $option = MenuOption::create([
            'menu_id' => $otherMenu->id,
            'name' => '非適用オプション',
            'price' => 500,
            'duration' => 30,
            'is_active' => true,
        ]);

        $date = now('Asia/Tokyo')->addDays(6)->startOfDay();
        $this->createBusinessHour($date, '10:00:00', '20:00:00');

        $start = $date->copy()->setTime(11, 0, 0);
        $end = $date->copy()->setTime(13, 0, 0);

        Livewire::actingAs($admin)
            ->test(ManageReservationCalendar::class)
            ->set('directReservationUserId', $customer->id)
            ->set('directReservationIsOther', true)
            ->set('directReservationOtherMenuName', 'その他メニュー（入力）')
            ->set('directReservationOptionIds', [$option->id])
            ->call('createDirectReservationFromCalendar', $start->toIso8601String(), $end->toIso8601String());

        $reservation = Reservation::query()->where('user_id', $customer->id)->latest('id')->first();

        $this->assertNotNull($reservation);
        $this->assertSame($otherMenu->id, $reservation->menu_id);
        $this->assertSame('11:00', $reservation->start_time->format('H:i'));
        $this->assertSame('13:00', $reservation->end_time->format('H:i'));
        $this->assertSame('confirmed', $reservation->status);
        $this->assertSame([], $reservation->options()->pluck('menu_option_id')->all());
    }

    public function test_other_mode_direct_reservation_is_rejected_when_other_menu_does_not_exist(): void
    {
        $admin = User::factory()->create([
            'is_admin' => true,
        ]);
        $customer = User::factory()->create();

        $date = now('Asia/Tokyo')->addDays(7)->startOfDay();
        $this->createBusinessHour($date, '10:00:00', '20:00:00');

        $start = $date->copy()->setTime(11, 0, 0);
        $end = $date->copy()->setTime(12, 0, 0);

        Livewire::actingAs($admin)
            ->test(ManageReservationCalendar::class)
            ->set('directReservationUserId', $customer->id)
            ->set('directReservationIsOther', true)
            ->set('directReservationOtherMenuName', 'その他メニュー（入力）')
            ->call('createDirectReservationFromCalendar', $start->toIso8601String(), $end->toIso8601String());

        $this->assertDatabaseMissing('reservations', [
            'user_id' => $customer->id,
            'date' => $date->toDateString(),
            'start_time' => '11:00:00',
            'status' => 'confirmed',
        ]);
    }

    public function test_other_mode_direct_reservation_is_rejected_when_time_overlaps_existing_reservation(): void
    {
        $admin = User::factory()->create([
            'is_admin' => true,
        ]);
        $customer = User::factory()->create();

        $otherMenu = Menu::factory()->create([
            'name' => 'その他',
            'is_event' => false,
            'duration' => 30,
            'is_active' => true,
        ]);

        $date = now('Asia/Tokyo')->addDays(8)->startOfDay();
        $this->createBusinessHour($date, '10:00:00', '20:00:00');

        Reservation::create([
            'user_id' => $customer->id,
            'menu_id' => $otherMenu->id,
            'slot_id' => null,
            'date' => $date->toDateString(),
            'start_time' => '11:30',
            'end_time' => '12:30',
            'status' => 'confirmed',
        ]);

        Livewire::actingAs($admin)
            ->test(ManageReservationCalendar::class)
            ->set('directReservationUserId', $customer->id)
            ->set('directReservationIsOther', true)
            ->set('directReservationOtherMenuName', '重複するその他')
            ->call(
                'createDirectReservationFromCalendar',
                $date->copy()->setTime(11, 0, 0)->toIso8601String(),
                $date->copy()->setTime(12, 0, 0)->toIso8601String(),
            );

        $this->assertSame(1, Reservation::query()->whereDate('date', $date->toDateString())->where('status', 'confirmed')->count());
    }

    public function test_other_mode_direct_reservation_is_rejected_when_time_block_conflicts(): void
    {
        $admin = User::factory()->create([
            'is_admin' => true,
        ]);
        $customer = User::factory()->create();

        Menu::factory()->create([
            'name' => 'その他',
            'is_event' => false,
            'duration' => 30,
            'is_active' => true,
        ]);

        $date = now('Asia/Tokyo')->addDays(9)->startOfDay();
        $this->createBusinessHour($date, '10:00:00', '20:00:00');

        TimeBlock::create([
            'start_at' => $date->copy()->setTime(10, 30, 0),
            'end_at' => $date->copy()->setTime(12, 0, 0),
            'reason' => '臨時対応',
            'is_active' => true,
        ]);

        Livewire::actingAs($admin)
            ->test(ManageReservationCalendar::class)
            ->set('directReservationUserId', $customer->id)
            ->set('directReservationIsOther', true)
            ->set('directReservationOtherMenuName', 'ブロック重複')
            ->call(
                'createDirectReservationFromCalendar',
                $date->copy()->setTime(11, 0, 0)->toIso8601String(),
                $date->copy()->setTime(12, 0, 0)->toIso8601String(),
            );

        $this->assertDatabaseMissing('reservations', [
            'user_id' => $customer->id,
            'date' => $date->toDateString(),
            'start_time' => '11:00:00',
            'status' => 'confirmed',
        ]);
    }

    private function createBusinessHour(Carbon $date, string $openTime, string $closeTime, bool $isClosed = false): void
    {
        BusinessHour::create([
            'day_of_week' => null,
            'specific_date' => $date->toDateString(),
            'open_time' => $openTime,
            'close_time' => $closeTime,
            'is_closed' => $isClosed,
        ]);
    }
}
