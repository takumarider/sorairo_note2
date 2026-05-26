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

    private function createBusinessHour(Carbon $date, string $openTime, string $closeTime): void
    {
        BusinessHour::create([
            'day_of_week' => null,
            'specific_date' => $date->toDateString(),
            'open_time' => $openTime,
            'close_time' => $closeTime,
            'is_closed' => false,
        ]);
    }
}
