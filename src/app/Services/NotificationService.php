<?php

namespace App\Services;

use App\Mail\AdminReservationNotification;
use App\Models\Reservation;
use App\Models\SystemSetting;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class NotificationService
{
    /**
     * 管理者に予約通知を送信
     */
    public function sendAdminNotification(Reservation $reservation, string $type): void
    {
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
