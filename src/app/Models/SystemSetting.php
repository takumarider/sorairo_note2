<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SystemSetting extends Model
{
    /** @use HasFactory<\Database\Factories\SystemSettingFactory> */
    use HasFactory;

    protected $fillable = [
        'admin_notification_email',
        'notification_from_email',
        'notification_from_name',
        'user_cancel_deadline_hours',
        'mail_user_confirmed_subject',
        'mail_user_confirmed_body',
        'mail_user_canceled_subject',
        'mail_user_canceled_body',
        'mail_admin_confirmed_subject',
        'mail_admin_confirmed_body',
        'mail_admin_canceled_subject',
        'mail_admin_canceled_body',
        'mail_event_user_confirmed_subject',
        'mail_event_user_confirmed_body',
        'mail_event_user_canceled_subject',
        'mail_event_user_canceled_body',
        'mail_event_admin_confirmed_subject',
        'mail_event_admin_confirmed_body',
        'mail_event_admin_canceled_subject',
        'mail_event_admin_canceled_body',
        'welcome_badge',
        'welcome_title',
        'welcome_subtitle',
        'welcome_lead',
        'welcome_main_image_path',
        'welcome_body_blocks',
        'welcome_shop_title',
        'welcome_shop_description',
        'welcome_business_hours',
        'welcome_regular_holiday',
        'welcome_contact_number',
        'welcome_business_note',
        'welcome_instagram_url',
    ];

    protected $casts = [
        'user_cancel_deadline_hours' => 'integer',
        'welcome_body_blocks' => 'array',
    ];

    public static function getSingleton(): self
    {
        $singleton = static::query()->find(1);

        if ($singleton) {
            return $singleton;
        }

        $legacy = static::query()->orderBy('id')->first();

        if ($legacy) {
            if ($legacy->id !== 1) {
                static::query()->whereKey($legacy->id)->update(['id' => 1]);

                return static::query()->findOrFail(1);
            }

            return $legacy;
        }

        return static::query()->forceCreate(['id' => 1]);
    }

    public function hasNotificationFrom(): bool
    {
        return filled($this->notification_from_email) && filled($this->notification_from_name);
    }

    public function hasAdminNotificationSettings(): bool
    {
        return filled($this->admin_notification_email) && $this->hasNotificationFrom();
    }

    public function userCancelDeadlineHours(): int
    {
        $hours = $this->user_cancel_deadline_hours;

        if (! is_int($hours) || $hours < 0) {
            return 24;
        }

        return $hours;
    }
}
