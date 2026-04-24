<?php

namespace Tests\Feature;

use App\Models\BusinessHour;
use App\Models\Menu;
use App\Models\Reservation;
use App\Models\ReservationPublicationMonth;
use App\Models\Slot;
use App\Models\User;
use App\Services\AvailabilityService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class EventReservationFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Mail::fake();

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

    public function test_event_menu_uses_slot_capacity_for_availability(): void
    {
        $menu = Menu::factory()->create([
            'is_event' => true,
            'duration' => 0,
        ]);

        $slot = Slot::create([
            'menu_id' => $menu->id,
            'date' => now('Asia/Tokyo')->addDay()->toDateString(),
            'start_time' => '10:00',
            'end_time' => '11:00',
            'capacity' => 2,
            'is_reserved' => false,
        ]);

        Reservation::create([
            'user_id' => User::factory()->create()->id,
            'menu_id' => $menu->id,
            'slot_id' => $slot->id,
            'date' => $slot->date->toDateString(),
            'start_time' => '10:00',
            'end_time' => '11:00',
            'status' => 'confirmed',
        ]);

        $service = new AvailabilityService;
        $result = $service->getAvailableTimesWithReason($menu, [], $slot->date->toDateString());

        $this->assertSame(['10:00'], $result['times']);
        $this->assertSame(1, $result['slot_details']['10:00']['remaining_capacity']);

        Reservation::create([
            'user_id' => User::factory()->create()->id,
            'menu_id' => $menu->id,
            'slot_id' => $slot->id,
            'date' => $slot->date->toDateString(),
            'start_time' => '10:00',
            'end_time' => '11:00',
            'status' => 'confirmed',
        ]);

        $filled = $service->getAvailableTimesWithReason($menu, [], $slot->date->toDateString());

        $this->assertSame([], $filled['times']);
        $this->assertSame('fully_booked', $filled['reason']);
    }

    public function test_user_can_store_event_reservation_from_standard_flow(): void
    {
        $user = User::factory()->create();
        $menu = Menu::factory()->create([
            'name' => '春のイベント',
            'is_event' => true,
            'duration' => 0,
            'price' => 2500,
        ]);
        $date = now('Asia/Tokyo')->addDay()->toDateString();

        $slot = Slot::create([
            'menu_id' => $menu->id,
            'date' => $date,
            'start_time' => '14:00',
            'end_time' => '15:30',
            'capacity' => 3,
            'is_reserved' => false,
        ]);

        $confirmResponse = $this->actingAs($user)->get(route('reservations.confirm', [
            'menu_id' => $menu->id,
            'date' => $date,
            'start_time' => '14:00',
        ]));

        $confirmResponse->assertOk();
        $confirmResponse->assertSee('name="slot_id" value="'.$slot->id.'"', false);

        $storeResponse = $this->actingAs($user)->post(route('reservations.store'), [
            'menu_id' => $menu->id,
            'date' => $date,
            'start_time' => '14:00',
            'slot_id' => $slot->id,
        ]);

        $reservation = Reservation::query()
            ->where('user_id', $user->id)
            ->where('slot_id', $slot->id)
            ->first();

        $this->assertNotNull($reservation);
        $this->assertSame('15:30', $reservation->end_time->format('H:i'));
        $storeResponse->assertRedirect(route('reservations.complete', ['reservation' => $reservation->id]));
    }

    public function test_user_cannot_book_same_event_twice_and_sees_thank_you_message(): void
    {
        $user = User::factory()->create();
        $menu = Menu::factory()->create([
            'is_event' => true,
            'duration' => 0,
        ]);
        $date = now('Asia/Tokyo')->addDay()->toDateString();

        $firstSlot = Slot::create([
            'menu_id' => $menu->id,
            'date' => $date,
            'start_time' => '10:00',
            'end_time' => '11:00',
            'capacity' => 2,
            'is_reserved' => false,
        ]);

        Slot::create([
            'menu_id' => $menu->id,
            'date' => $date,
            'start_time' => '13:00',
            'end_time' => '14:00',
            'capacity' => 2,
            'is_reserved' => false,
        ]);

        Reservation::create([
            'user_id' => $user->id,
            'menu_id' => $menu->id,
            'slot_id' => $firstSlot->id,
            'date' => $date,
            'start_time' => '10:00',
            'end_time' => '11:00',
            'status' => 'confirmed',
        ]);

        $timesResponse = $this->actingAs($user)->get(route('reservations.times', [
            'menu_id' => $menu->id,
            'date' => $date,
        ]));

        // 全スロットが表示されるが、いずれも非活性（name="start_time" ボタンなし）
        $timesResponse->assertOk();
        $timesResponse->assertSee('ご予約ありがとうございます。');
        $timesResponse->assertDontSee('name="start_time"', false);

        // POST 直打ちでも拒否される
        $secondStore = $this->actingAs($user)->post(route('reservations.store'), [
            'menu_id' => $menu->id,
            'date' => $date,
            'start_time' => '13:00',
        ]);

        $secondStore->assertSessionHasErrors('start_time');
        $this->assertSame(1, Reservation::query()->where('user_id', $user->id)->where('menu_id', $menu->id)->whereDate('date', $date)->where('status', 'confirmed')->count());
    }
}
