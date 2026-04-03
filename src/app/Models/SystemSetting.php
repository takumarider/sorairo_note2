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
        'welcome_business_note',
        'welcome_instagram_url',
    ];

    protected $casts = [
        'welcome_body_blocks' => 'array',
    ];

    public static function getSingleton(): self
    {
        return static::firstOrCreate([]);
    }

    public function hasNotificationFrom(): bool
    {
        return filled($this->notification_from_email) && filled($this->notification_from_name);
    }

    public function hasAdminNotificationSettings(): bool
    {
        return filled($this->admin_notification_email) && $this->hasNotificationFrom();
    }
}
