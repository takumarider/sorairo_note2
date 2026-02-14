<?php

namespace Tests\Feature;

use App\Models\Menu;
use App\Models\Reservation;
use App\Models\Slot;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ReservationOverlapTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_cannot_reserve_overlapping_time_on_different_menu(): void
    {
        Mail::fake();

        $menuA = Menu::factory()->create();
        $menuB = Menu::factory()->create();

        $date = now()->addDay()->toDateString();

        $slotA = Slot::create([
            'menu_id' => $menuA->id,
            'date' => $date,
            'start_time' => '10:00:00',
            'end_time' => '11:00:00',
            'is_reserved' => true,
        ]);

        Reservation::create([
            'user_id' => User::factory()->create()->id,
            'menu_id' => $menuA->id,
            'slot_id' => $slotA->id,
            'status' => 'confirmed',
        ]);

        $slotB = Slot::create([
            'menu_id' => $menuB->id,
            'date' => $date,
            'start_time' => '10:30:00',
            'end_time' => '11:30:00',
            'is_reserved' => false,
        ]);

        $user = User::factory()->create();
        $response = $this->actingAs($user)->post('/reservations', [
            'slot_id' => $slotB->id,
        ]);

        $response->assertRedirect(route('menus.index'));
        $response->assertSessionHas('error', 'この時間帯は既に予約されています。');

        $this->assertDatabaseCount('reservations', 1);
        $this->assertDatabaseHas('slots', [
            'id' => $slotB->id,
            'is_reserved' => false,
        ]);
    }

    public function test_user_can_reserve_when_time_does_not_overlap(): void
    {
        Mail::fake();

        $menuA = Menu::factory()->create();
        $menuB = Menu::factory()->create();

        $date = now()->addDay()->toDateString();

        $slotA = Slot::create([
            'menu_id' => $menuA->id,
            'date' => $date,
            'start_time' => '10:00:00',
            'end_time' => '11:00:00',
            'is_reserved' => true,
        ]);

        Reservation::create([
            'user_id' => User::factory()->create()->id,
            'menu_id' => $menuA->id,
            'slot_id' => $slotA->id,
            'status' => 'confirmed',
        ]);

        $slotB = Slot::create([
            'menu_id' => $menuB->id,
            'date' => $date,
            'start_time' => '11:00:00',
            'end_time' => '12:00:00',
            'is_reserved' => false,
        ]);

        $user = User::factory()->create();
        $response = $this->actingAs($user)->post('/reservations', [
            'slot_id' => $slotB->id,
        ]);

        $reservation = Reservation::where('user_id', $user->id)
            ->where('slot_id', $slotB->id)
            ->first();

        $this->assertNotNull($reservation);

        $response->assertRedirect(route('reservations.complete', ['reservation' => $reservation->id]));

        $this->assertDatabaseHas('reservations', [
            'user_id' => $user->id,
            'menu_id' => $menuB->id,
            'slot_id' => $slotB->id,
            'status' => 'confirmed',
        ]);

        $this->assertDatabaseHas('slots', [
            'id' => $slotB->id,
            'is_reserved' => true,
        ]);
    }
}
