<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BusinessHour extends Model
{
    /** @use HasFactory<\Database\Factories\BusinessHourFactory> */
    use HasFactory;

    protected $fillable = [
        'day_of_week',
        'specific_date',
        'open_time',
        'close_time',
        'is_closed',
    ];

    protected function casts(): array
    {
        return [
            'specific_date' => 'date',
            'is_closed' => 'boolean',
        ];
    }

    /**
     * 指定日付の営業時間設定を取得（休業設定を含む）
     *
     * @param Carbon $date
     * @return self|null
     */
    public static function getSettingForDate(Carbon $date): ?self
    {
        $specific = self::where('specific_date', $date->toDateString())
            ->first();

        if ($specific) {
            return $specific;
        }

        $dayOfWeek = $date->dayOfWeek;

        return self::where('day_of_week', $dayOfWeek)
            ->whereNull('specific_date')
            ->first();
    }

    /**
     * 指定日付の営業時間を取得（特定日優先）
     *
     * @param Carbon $date
     * @return self|null
     */
    public static function getForDate(Carbon $date): ?self
    {
        $setting = self::getSettingForDate($date);

        if ($setting && ! $setting->is_closed) {
            return $setting;
        }

        return null;
    }

    /**
     * 指定日付が営業日かどうかを判定
     *
     * @param Carbon $date
     * @return bool
     */
    public static function isOpenOnDate(Carbon $date): bool
    {
        return self::getForDate($date) !== null;
    }
}
