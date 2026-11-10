<?php

namespace Tests\Feature;

use App\Models\BusinessHour;
use App\Models\Menu;
use App\Models\MenuOption;
use App\Models\Reservation;
use App\Models\ReservationPublicationMonth;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class MenuSameDayReservationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow(Carbon::create(2026, 6, 8, 9, 40, 0, 'Asia/Tokyo'));
        Mail::fake();

        for ($day = 0; $day < 7; $day++) {
            BusinessHour::create([
                'day_of_week' => $day,
                'open_time' => '10:00',
                'close_time' => '12:00',
                'is_closed' => false,
            ]);
        }

        ReservationPublicationMonth::updateOrCreate(
            ['year_month' => '2026-06'],
            ['is_published' => true],
        );
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    private function optionFor(Menu $menu): MenuOption
    {
        return MenuOption::create([
            'menu_id' => $menu->id,
            'name' => '追加オプション',
            'price' => 1000,
            'duration' => 30,
            'is_active' => true,
        ]);
    }

    public function test_header_has_no_same_day_link_for_guests_or_authenticated_users(): void
    {
        $this->get(route('menus.index'))->assertOk()
            ->assertDontSee(route('reservations.same-day.times'));

        $user = User::factory()->create();
        $this->actingAs($user)->get(route('menus.index'))->assertOk()
            ->assertDontSee(route('reservations.same-day.times'))
            ->assertDontSee('今日予約する')
            ->assertSee('grid grid-cols-3', false)
            ->assertSee('マイページ');
    }

    public function test_menu_page_has_two_same_day_submit_buttons_but_event_page_has_none(): void
    {
        $menu = Menu::factory()->create();
        $event = Menu::factory()->create(['is_event' => true]);
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('menus.show', $menu));
        $response->assertOk()
            ->assertSee('action="'.route('reservations.start').'"', false)
            ->assertSee('@submit="syncHiddenOptions()"', false)
            ->assertDontSee(route('reservations.same-day.times'));
        $this->assertSame(2, substr_count($response->getContent(), 'name="booking_type" value="same_day"'));

        $this->get(route('menus.show', $event))->assertOk()
            ->assertDontSee('name="booking_type" value="same_day"', false);
    }

    public function test_same_day_badge_uses_menu_availability_for_guests_and_users(): void
    {
        $menu = Menu::factory()->create(['duration' => 60]);
        $response = $this->get(route('menus.show', $menu))->assertOk();
        $this->assertSame(2, substr_count($response->getContent(), '>受付中</span>'));
        $this->getJson(route('menus.same-day-availability', $menu))
            ->assertOk()->assertExactJson(['available' => true])
            ->assertHeader('Cache-Control', 'no-store, private');

        Carbon::setTestNow(Carbon::create(2026, 6, 8, 11, 0, 0, 'Asia/Tokyo'));
        $user = User::factory()->create();
        $response = $this->actingAs($user)->get(route('menus.show', $menu))->assertOk();
        $this->assertSame(2, substr_count($response->getContent(), '>準備中</span>'));
        $this->getJson(route('menus.same-day-availability', $menu))
            ->assertOk()->assertExactJson(['available' => false]);
    }

    public function test_same_day_badge_accounts_for_selected_options_and_month_publication(): void
    {
        $menu = Menu::factory()->create(['duration' => 60]);
        $option = $this->optionFor($menu);
        $option->update(['duration' => 60]);
        $this->getJson(route('menus.same-day-availability', $menu))
            ->assertOk()->assertExactJson(['available' => true]);
        $this->getJson(route('menus.same-day-availability', ['menu' => $menu, 'options' => [$option->id]]))
            ->assertOk()->assertExactJson(['available' => false]);

        ReservationPublicationMonth::where('year_month', '2026-06')->update(['is_published' => false]);
        $this->getJson(route('menus.same-day-availability', $menu))
            ->assertOk()->assertExactJson(['available' => false]);
    }

    public function test_same_day_availability_rejects_invalid_options_and_event_or_inactive_menus(): void
    {
        $menu = Menu::factory()->create();
        $unrelatedOption = $this->optionFor(Menu::factory()->create());
        $inactiveOption = $this->optionFor($menu);
        $inactiveOption->update(['is_active' => false]);

        foreach ([$unrelatedOption, $inactiveOption] as $option) {
            $this->getJson(route('menus.same-day-availability', ['menu' => $menu, 'options' => [$option->id]]))
                ->assertUnprocessable()->assertJsonValidationErrors('options.0');
        }

        foreach ([['is_event' => true], ['is_active' => false]] as $attributes) {
            $invalidMenu = Menu::factory()->create($attributes);
            $this->getJson(route('menus.same-day-availability', $invalidMenu))->assertNotFound();
        }
    }

    public function test_same_day_availability_respects_existing_reservations(): void
    {
        $menu = Menu::factory()->create(['duration' => 60]);
        Reservation::create([
            'user_id' => User::factory()->create()->id,
            'menu_id' => $menu->id,
            'date' => '2026-06-08',
            'start_time' => '10:30',
            'end_time' => '12:00',
            'status' => 'confirmed',
        ]);
        $this->getJson(route('menus.same-day-availability', $menu))
            ->assertOk()->assertExactJson(['available' => false]);
    }

    public function test_selected_menu_and_options_are_used_for_same_day_times_and_booking(): void
    {
        $menu = Menu::factory()->create(['name' => '選択メニュー', 'duration' => 60, 'price' => 3000]);
        $option = $this->optionFor($menu);
        $user = User::factory()->create();
        $selection = ['menu_id' => $menu->id, 'options' => [$option->id]];
        $this->actingAs($user)->get(route('reservations.start', $selection + ['booking_type' => 'same_day']))
            ->assertRedirect(route('reservations.same-day.selected', $selection));

        $this->get(route('reservations.same-day.selected', $selection))
            ->assertRedirect(route('reservations.times', ['menu_id' => $menu->id, 'date' => '2026-06-08', 'options' => [$option->id]]));

        $this->get(route('reservations.times', $selection + ['date' => '2026-06-08']))
            ->assertOk()
            ->assertSee('選択メニュー')
            ->assertSee('追加オプション')
            ->assertSee('90分')
            ->assertDontSee('value="10:00"', false)
            ->assertSee('value="10:30"', false)
            ->assertDontSee('value="11:00"', false)
            ->assertSee('name="options[]" value="'.$option->id.'"', false);

        $confirm = $selection + ['date' => '2026-06-08', 'start_time' => '10:30'];
        $this->get(route('reservations.confirm', $confirm))->assertOk()
            ->assertSee('追加オプション')
            ->assertSee('90分')
            ->assertSee('name="options[]" value="'.$option->id.'"', false)
            ->assertSee('href="'.e(route('reservations.calendar', $selection)).'"', false);

        $response = $this->post(route('reservations.store'), $confirm);
        $reservation = Reservation::query()->where('user_id', $user->id)->firstOrFail();
        $response->assertRedirect(route('reservations.complete', $reservation));
        $this->assertSame(90, $reservation->total_duration);
        $this->assertSame(4000, $reservation->total_price);
        $this->assertTrue($reservation->options->contains($option));
    }

    public function test_same_day_entry_without_options_and_normal_booking_still_work(): void
    {
        $menu = Menu::factory()->create();
        $user = User::factory()->create();
        $selection = ['menu_id' => $menu->id];

        $this->actingAs($user)->get(route('reservations.start', $selection))
            ->assertRedirect(route('reservations.calendar', $selection));
        $this->get(route('reservations.start', $selection + ['booking_type' => 'same_day']))
            ->assertRedirect(route('reservations.same-day.selected', $selection));
        $this->get(route('reservations.same-day.selected', $selection))
            ->assertRedirect(route('reservations.times', $selection + ['date' => '2026-06-08']));
    }

    public function test_guest_login_preserves_selection_and_calculates_date_after_login(): void
    {
        $menu = Menu::factory()->create();
        $option = $this->optionFor($menu);
        $user = User::factory()->create();
        $selection = ['menu_id' => $menu->id, 'options' => [$option->id]];

        $this->get(route('reservations.start', $selection + ['booking_type' => 'same_day']))
            ->assertRedirect(route('login'))
            ->assertSessionHas('url.intended', route('reservations.same-day.selected', $selection, false));

        Carbon::setTestNow(Carbon::create(2026, 6, 9, 9, 40, 0, 'Asia/Tokyo'));
        $this->post(route('login'), ['email' => $user->email, 'password' => 'password'])
            ->assertRedirect(route('reservations.same-day.selected', $selection, false));
        $this->get(route('reservations.same-day.selected', $selection))
            ->assertRedirect(route('reservations.times', ['menu_id' => $menu->id, 'date' => '2026-06-09', 'options' => [$option->id]]));
    }

    public function test_guest_registration_preserves_same_day_selection(): void
    {
        $menu = Menu::factory()->create();
        $option = $this->optionFor($menu);
        $selection = ['menu_id' => $menu->id, 'options' => [$option->id]];

        $this->get(route('reservations.start', $selection + ['booking_type' => 'same_day']))
            ->assertRedirect(route('login'));
        $this->post(route('register'), [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'Str0ng!Pass#2026',
            'password_confirmation' => 'Str0ng!Pass#2026',
        ])->assertRedirect(route('reservations.same-day.selected', $selection, false));
    }

    public function test_selected_entry_rejects_event_inactive_menu_and_unrelated_or_inactive_options(): void
    {
        $user = User::factory()->create();
        $menu = Menu::factory()->create();
        $event = Menu::factory()->create(['is_event' => true]);
        $inactive = Menu::factory()->create(['is_active' => false]);
        $unrelatedOption = $this->optionFor(Menu::factory()->create());
        $inactiveOption = $this->optionFor($menu);
        $inactiveOption->update(['is_active' => false]);
        $this->actingAs($user);

        foreach ([$event, $inactive] as $invalidMenu) {
            $this->get(route('reservations.same-day.selected', ['menu_id' => $invalidMenu->id]))
                ->assertSessionHasErrors('menu_id');
        }
        foreach ([$unrelatedOption, $inactiveOption] as $invalidOption) {
            $this->get(route('reservations.same-day.selected', [
                'menu_id' => $menu->id,
                'options' => [$invalidOption->id],
            ]))->assertSessionHasErrors('options.0');
        }
    }

    public function test_unpublished_month_redirects_to_calendar_with_selection(): void
    {
        ReservationPublicationMonth::where('year_month', '2026-06')->update(['is_published' => false]);
        $menu = Menu::factory()->create();
        $option = $this->optionFor($menu);
        $user = User::factory()->create();
        $selection = ['menu_id' => $menu->id, 'options' => [$option->id]];

        $this->actingAs($user)->get(route('reservations.times', $selection + ['date' => '2026-06-08']))
            ->assertRedirect(route('reservations.calendar', $selection + ['month' => '2026-06']))
            ->assertSessionHas('availability_reason', 'month_unpublished');
    }

    public function test_unavailable_time_returns_to_menu_specific_times_with_options(): void
    {
        $menu = Menu::factory()->create(['duration' => 60]);
        $option = $this->optionFor($menu);
        $user = User::factory()->create();
        $selection = ['menu_id' => $menu->id, 'options' => [$option->id], 'date' => '2026-06-08'];

        $this->actingAs($user)->get(route('reservations.confirm', $selection + ['start_time' => '10:00']))
            ->assertRedirect(route('reservations.times', ['menu_id' => $menu->id, 'date' => '2026-06-08', 'options' => [$option->id]]))
            ->assertSessionHasErrors('start_time');
        $this->get(route('reservations.times', $selection))
            ->assertOk()
            ->assertSee('role="alert"', false)
            ->assertSee('追加オプション');
    }

    public function test_booking_conflict_keeps_selection_when_returning_from_confirmation(): void
    {
        $menu = Menu::factory()->create(['duration' => 60]);
        $option = $this->optionFor($menu);
        $user = User::factory()->create();
        $selection = ['menu_id' => $menu->id, 'date' => '2026-06-08', 'options' => [$option->id]];
        $confirm = $selection + ['start_time' => '10:30'];
        $this->actingAs($user)->get(route('reservations.confirm', $confirm))->assertOk();

        Reservation::create([
            'user_id' => User::factory()->create()->id,
            'menu_id' => $menu->id,
            'date' => '2026-06-08',
            'start_time' => '10:30',
            'end_time' => '11:30',
            'status' => 'confirmed',
        ]);

        $this->from(route('reservations.confirm', $confirm))
            ->post(route('reservations.store'), $confirm)
            ->assertRedirect(route('reservations.confirm', $confirm))
            ->assertSessionHasErrors('start_time');
        $this->get(route('reservations.confirm', $confirm))
            ->assertRedirect(route('reservations.times', $selection))
            ->assertSessionHasErrors('start_time');
        $this->get(route('reservations.times', $selection))
            ->assertOk()
            ->assertSee('追加オプション')
            ->assertSee('role="alert"', false);
        $this->assertDatabaseCount('reservations', 1);
    }
}
