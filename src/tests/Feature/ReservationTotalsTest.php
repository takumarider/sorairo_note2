<?php

namespace Tests\Feature;

use App\Models\BusinessHour;
use App\Models\Menu;
use App\Models\MenuOption;
use App\Models\Reservation;
use App\Models\ReservationPublicationMonth;
use App\Models\Slot;
use App\Models\User;
use App\Services\AvailabilityService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ReservationTotalsTest extends TestCase
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

        ReservationPublicationMonth::create([
            'year_month' => now('Asia/Tokyo')->startOfMonth()->addMonth()->format('Y-m'),
            'is_published' => true,
        ]);
    }

    public function test_menu_option_allows_negative_price_and_duration(): void
    {
        $menu = Menu::factory()->create(['price' => 5000, 'duration' => 60]);

        $option = MenuOption::create([
            'menu_id' => $menu->id,
            'name' => '割引オプション',
            'price' => -1000,
            'duration' => -10,
            'image_path' => 'menu-options/dummy.png',
            'is_active' => true,
        ]);

        $this->assertSame(-1000, $option->price);
        $this->assertSame(-10, $option->duration);
        $this->assertSame('-¥1,000', $option->priceLabel());
        $this->assertSame('-10分', $option->durationLabel());
    }

    public function test_calculate_totals_clamps_at_zero_for_non_event_menu_with_negative_options(): void
    {
        $menu = Menu::factory()->create(['price' => 500, 'duration' => 10, 'is_event' => false]);

        $option = MenuOption::create([
            'menu_id' => $menu->id,
            'name' => '大幅割引',
            'price' => -2000,
            'duration' => -30,
            'image_path' => 'menu-options/dummy.png',
            'is_active' => true,
        ]);

        $service = new AvailabilityService;
        $totals = $service->calculateTotals($menu, collect([$option]));

        $this->assertSame(0, $totals['price']);
        $this->assertSame(0, $totals['duration']);
    }

    public function test_calculate_totals_ignores_options_for_event_menu(): void
    {
        $menu = Menu::factory()->create(['price' => 3000, 'duration' => 0, 'is_event' => true]);

        $option = MenuOption::create([
            'menu_id' => $menu->id,
            'name' => '無視されるオプション',
            'price' => 1000,
            'duration' => 15,
            'image_path' => 'menu-options/dummy.png',
            'is_active' => true,
        ]);

        $service = new AvailabilityService;
        $totals = $service->calculateTotals($menu, collect([$option]), 90);

        $this->assertSame(3000, $totals['price']);
        $this->assertSame(90, $totals['duration']);
    }

    public function test_store_persists_clamped_total_price_and_duration_for_treatment_menu(): void
    {
        $user = User::factory()->create();
        $menu = Menu::factory()->create(['price' => 500, 'duration' => 30, 'is_event' => false]);

        $discountOption = MenuOption::create([
            'menu_id' => $menu->id,
            'name' => '大幅割引・時短オプション',
            'price' => -2000,
            'duration' => -40,
            'image_path' => 'menu-options/dummy.png',
            'is_active' => true,
        ]);

        $date = now('Asia/Tokyo')->addDay()->toDateString();

        $response = $this->actingAs($user)->post(route('reservations.store'), [
            'menu_id' => $menu->id,
            'date' => $date,
            'start_time' => '11:00',
            'options' => [$discountOption->id],
        ]);

        $reservation = Reservation::query()->where('user_id', $user->id)->first();

        $this->assertNotNull($reservation);
        $response->assertRedirect(route('reservations.complete', ['reservation' => $reservation->id]));
        $this->assertSame(0, $reservation->total_price);
        $this->assertSame(0, $reservation->total_duration);
        $this->assertSame($reservation->start_time->format('H:i'), $reservation->end_time->format('H:i'));
    }

    public function test_store_persists_total_price_and_duration_for_event_menu_and_excludes_options(): void
    {
        $user = User::factory()->create();
        $menu = Menu::factory()->create(['price' => 2500, 'duration' => 0, 'is_event' => true]);

        $option = MenuOption::create([
            'menu_id' => $menu->id,
            'name' => '追加オプション',
            'price' => 1000,
            'duration' => 15,
            'image_path' => 'menu-options/dummy.png',
            'is_active' => true,
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

        $response = $this->actingAs($user)->post(route('reservations.store'), [
            'menu_id' => $menu->id,
            'date' => $date,
            'start_time' => '14:00',
            'slot_id' => $slot->id,
            'options' => [$option->id],
        ]);

        $reservation = Reservation::query()->where('user_id', $user->id)->first();

        $this->assertNotNull($reservation);
        $response->assertRedirect(route('reservations.complete', ['reservation' => $reservation->id]));
        $this->assertSame(2500, $reservation->total_price);
        $this->assertSame(90, $reservation->total_duration);
        $this->assertCount(0, $reservation->options);
    }

    public function test_resolved_total_price_and_duration_fall_back_for_legacy_reservations_without_persisted_totals(): void
    {
        $user = User::factory()->create();
        $menu = Menu::factory()->create(['price' => 4000, 'duration' => 45, 'is_event' => false]);

        $option = MenuOption::create([
            'menu_id' => $menu->id,
            'name' => 'レガシーオプション',
            'price' => 500,
            'duration' => 10,
            'image_path' => 'menu-options/dummy.png',
            'is_active' => true,
        ]);

        $reservation = Reservation::create([
            'user_id' => $user->id,
            'menu_id' => $menu->id,
            'slot_id' => null,
            'date' => now('Asia/Tokyo')->addDay()->toDateString(),
            'start_time' => '11:00',
            'end_time' => '12:00',
            'status' => 'confirmed',
            // total_price / total_duration are intentionally left null to
            // simulate a reservation created before this feature existed.
        ]);
        $reservation->options()->attach($option->id);

        $this->assertNull($reservation->total_price);
        $this->assertNull($reservation->total_duration);
        $this->assertSame(4500, $reservation->resolvedTotalPrice());
        $this->assertSame(60, $reservation->resolvedTotalDuration());
    }
}
