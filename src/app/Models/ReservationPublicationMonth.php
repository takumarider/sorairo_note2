<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReservationPublicationMonth extends Model
{
    /** @use HasFactory<\Database\Factories\ReservationPublicationMonthFactory> */
    use HasFactory;

    protected $fillable = [
        'year_month',
        'is_published',
    ];

    protected function casts(): array
    {
        return [
            'is_published' => 'boolean',
        ];
    }

    public static function isPublishedForMonth(Carbon $month): bool
    {
        return self::isPublishedYearMonth($month->format('Y-m'));
    }

    public static function isPublishedYearMonth(string $yearMonth): bool
    {
        return self::query()
            ->where('year_month', $yearMonth)
            ->where('is_published', true)
            ->exists();
    }
}
