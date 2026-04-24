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
            $settings = SystemSetting::getSingleton();
            $this->applyFromSettings($settings);

            $isEvent = $reservation->menu->is_event;
            $templateType = $isEvent ? 'event_user_confirmed' : 'user_confirmed';
            $template = $this->resolveTemplateContent($settings, $reservation, $templateType);

            Mail::to($reservation->user->email)
                ->send(new ReservationConfirmed($reservation, $template['subject'], $template['body']));
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
            $settings = SystemSetting::getSingleton();
            $this->applyFromSettings($settings);

            $isEvent = $reservation->menu->is_event;
            $templateType = $isEvent ? 'event_user_canceled' : 'user_canceled';
            $template = $this->resolveTemplateContent($settings, $reservation, $templateType);

            Mail::to($reservation->user->email)
                ->send(new ReservationCanceled($reservation, $template['subject'], $template['body']));
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
            $settings = SystemSetting::getSingleton();

            if (! $this->isAdminNotificationConfigured($settings)) {
                Log::warning('管理者通知設定が未設定のため送信をスキップしました', [
                    'reservation_id' => $reservation->id,
                    'type' => $type,
                ]);

                return;
            }

            $this->applyFromSettings($settings);

            $isEvent = $reservation->menu->is_event;
            $templateType = match (true) {
                $type === 'canceled' && $isEvent => 'event_admin_canceled',
                $type === 'canceled' => 'admin_canceled',
                $isEvent => 'event_admin_confirmed',
                default => 'admin_confirmed',
            };
            $template = $this->resolveTemplateContent($settings, $reservation, $templateType);

            Mail::to($settings->admin_notification_email)
                ->send(new AdminReservationNotification($reservation, $type, $template['subject'], $template['body']));
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

    /**
     * @return array{subject: ?string, body: ?string}
     */
    private function resolveTemplateContent(SystemSetting $settings, Reservation $reservation, string $type): array
    {
        $templates = [
            'user_confirmed' => [
                'subject' => $settings->mail_user_confirmed_subject,
                'body' => $settings->mail_user_confirmed_body,
            ],
            'user_canceled' => [
                'subject' => $settings->mail_user_canceled_subject,
                'body' => $settings->mail_user_canceled_body,
            ],
            'admin_confirmed' => [
                'subject' => $settings->mail_admin_confirmed_subject,
                'body' => $settings->mail_admin_confirmed_body,
            ],
            'admin_canceled' => [
                'subject' => $settings->mail_admin_canceled_subject,
                'body' => $settings->mail_admin_canceled_body,
            ],
            'event_user_confirmed' => [
                'subject' => $settings->mail_event_user_confirmed_subject,
                'body' => $settings->mail_event_user_confirmed_body,
            ],
            'event_user_canceled' => [
                'subject' => $settings->mail_event_user_canceled_subject,
                'body' => $settings->mail_event_user_canceled_body,
            ],
            'event_admin_confirmed' => [
                'subject' => $settings->mail_event_admin_confirmed_subject,
                'body' => $settings->mail_event_admin_confirmed_body,
            ],
            'event_admin_canceled' => [
                'subject' => $settings->mail_event_admin_canceled_subject,
                'body' => $settings->mail_event_admin_canceled_body,
            ],
        ];

        $target = $templates[$type] ?? ['subject' => null, 'body' => null];
        $variables = $this->buildTemplateVariables($reservation, $type);

        return [
            'subject' => $this->renderTemplate($target['subject'], $variables),
            'body' => $this->renderTemplate($target['body'], $variables),
        ];
    }

    /**
     * @return array<string, string>
     */
    private function buildTemplateVariables(Reservation $reservation, string $type): array
    {
        $reservation->loadMissing(['user', 'menu', 'slot']);

        $reservationDate = $reservation->date ?? $reservation->slot?->date;
        $reservationStart = $reservation->start_time ?? $reservation->slot?->start_time;
        $reservationEnd = $reservation->end_time ?? $reservation->slot?->end_time;

        return [
            'user_name' => (string) ($reservation->user->name ?? ''),
            'user_email' => (string) ($reservation->user->email ?? ''),
            'reservation_id' => (string) $reservation->id,
            'reservation_status' => $reservation->status === 'confirmed' ? '確定' : 'キャンセル',
            'reservation_date' => $reservationDate?->format('Y年m月d日') ?? '',
            'reservation_start_time' => $reservationStart?->format('H:i') ?? '',
            'reservation_end_time' => $reservationEnd?->format('H:i') ?? '',
            'menu_name' => (string) ($reservation->menu->name ?? ''),
            'menu_price' => number_format((int) ($reservation->menu->price ?? 0)),
            'menu_duration' => (string) ($reservation->menu->duration ?? ''),
            'event_type' => $type === 'admin_canceled' || $type === 'user_canceled' ? 'キャンセル' : '予約確定',
            'mypage_url' => route('mypage'),
            'new_reservation_url' => route('menus.index'),
            'admin_reservation_url' => config('app.url').'/admin/reservations/'.$reservation->id,
            'app_name' => (string) config('app.name'),
        ];
    }

    /**
     * テンプレート内の {{variable}} を予約情報に基づいて展開する。
     */
    private function renderTemplate(?string $template, array $variables): ?string
    {
        if (! filled($template)) {
            return null;
        }

        return (string) preg_replace_callback('/{{\s*([a-zA-Z0-9_]+)\s*}}/', function (array $matches) use ($variables): string {
            $key = $matches[1] ?? '';

            return $variables[$key] ?? '';
        }, $template);
    }
}
