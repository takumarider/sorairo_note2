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
