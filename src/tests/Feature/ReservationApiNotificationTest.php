<?php

namespace Tests\Feature;

use App\Mail\AdminReservationNotification;
use App\Mail\ReservationCanceled;
use App\Mail\ReservationConfirmed;
use App\Models\BusinessHour;
use App\Models\Menu;
use App\Models\Reservation;
use App\Models\ReservationPublicationMonth;
use App\Models\SystemSetting;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ReservationApiNotificationTest extends TestCase
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
            'year_month' => now('Asia/Tokyo')->addDay()->format('Y-m'),
            'is_published' => true,
        ]);

        SystemSetting::getSingleton()->update([
            'admin_notification_email' => 'admin@example.com',
            'notification_from_email' => 'info@sorairo-note-app.com',
            'notification_from_name' => 'Sorairo Note',
        ]);
    }

    public function test_api_store_sends_user_and_admin_notifications(): void
    {
        $user = User::factory()->create(['email' => 'customer@example.com']);
        $menu = Menu::factory()->create(['duration' => 60]);
        $date = now('Asia/Tokyo')->addDay()->toDateString();

        $response = $this->actingAs($user)->postJson('/api/reservations', [
            'menu_id' => $menu->id,
            'date' => $date,
            'start_time' => '11:00',
            'options' => [],
        ]);

        $response->assertOk();
        $response->assertJsonPath('success', true);

        Mail::assertSent(ReservationConfirmed::class, function (ReservationConfirmed $mail) use ($user): bool {
            return $mail->hasTo($user->email);
        });

        Mail::assertSent(AdminReservationNotification::class, function (AdminReservationNotification $mail): bool {
            return $mail->hasTo('admin@example.com') && $mail->type === 'confirmed';
        });
    }

    public function test_api_destroy_sends_user_and_admin_notifications(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 4, 17, 10, 0, 0, 'Asia/Tokyo'));

        $admin = User::factory()->create(['is_admin' => true]);
        $customer = User::factory()->create(['email' => 'customer@example.com']);
        $menu = Menu::factory()->create(['duration' => 60]);

        $reservation = Reservation::create([
            'user_id' => $customer->id,
            'menu_id' => $menu->id,
            'slot_id' => null,
            'date' => '2026-04-18',
            'start_time' => '11:00',
            'end_time' => '12:00',
            'status' => 'confirmed',
        ]);

        $response = $this->actingAs($admin)
            ->deleteJson('/api/reservations/'.$reservation->id);

        $response->assertOk();
        $response->assertJsonPath('success', true);

        Mail::assertSent(ReservationCanceled::class, function (ReservationCanceled $mail) use ($customer): bool {
            return $mail->hasTo($customer->email);
        });

        Mail::assertSent(AdminReservationNotification::class, function (AdminReservationNotification $mail): bool {
            return $mail->hasTo('admin@example.com') && $mail->type === 'canceled';
        });

        Carbon::setTestNow();
    }
}
