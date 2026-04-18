<?php

namespace App\Services;

use App\Mail\AdminReservationNotification;
use App\Mail\ReservationCanceled;
use App\Mail\ReservationConfirmed;
use App\Models\Reservation;
use App\Models\SystemSetting;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class NotificationService
{
    /**
     * ユーザーに予約確定通知を送信
     */
    public function sendReservationConfirmedToUser(Reservation $reservation): void
    {
        try {
            $settings = SystemSetting::first();
            $this->applyFromSettings($settings);

            Mail::to($reservation->user->email)
                ->send(new ReservationConfirmed($reservation));
        } catch (Throwable $e) {
            Log::warning('予約確定メールの送信に失敗しました', [
                'reservation_id' => $reservation->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * ユーザーに予約キャンセル通知を送信
     */
    public function sendReservationCanceledToUser(Reservation $reservation): void
    {
        try {
            $settings = SystemSetting::first();
            $this->applyFromSettings($settings);

            Mail::to($reservation->user->email)
                ->send(new ReservationCanceled($reservation));
        } catch (Throwable $e) {
            Log::warning('予約キャンセルメールの送信に失敗しました', [
                'reservation_id' => $reservation->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * 管理者に予約通知を送信
     */
    public function sendAdminNotification(Reservation $reservation, string $type): void
    {
        try {
            $settings = SystemSetting::first();

            if (! $this->isAdminNotificationConfigured($settings)) {
                Log::warning('管理者通知設定が未設定のため送信をスキップしました', [
                    'reservation_id' => $reservation->id,
                    'type' => $type,
                ]);

                return;
            }

            $this->applyFromSettings($settings);

            Mail::to($settings->admin_notification_email)
                ->send(new AdminReservationNotification($reservation, $type));
        } catch (Throwable $e) {
            Log::warning('管理者通知メールの送信に失敗しました', [
                'reservation_id' => $reservation->id,
                'type' => $type,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * 通知元設定があれば適用
     */
    public function applyFromSettings(?SystemSetting $settings): void
    {
        if (! $settings || ! $settings->hasNotificationFrom()) {
            return;
        }

        config([
            'mail.from.address' => $settings->notification_from_email,
            'mail.from.name' => $settings->notification_from_name,
        ]);
    }

    /**
     * 管理者通知設定が完了しているかチェック
     */
    private function isAdminNotificationConfigured(?SystemSetting $settings): bool
    {
        return $settings?->hasAdminNotificationSettings() ?? false;
    }
}
