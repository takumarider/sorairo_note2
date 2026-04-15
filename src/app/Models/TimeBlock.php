<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TimeBlock extends Model
{
    /** @use HasFactory<\Database\Factories\TimeBlockFactory> */
    use HasFactory;

    protected $fillable = [
        'start_at',
        'end_at',
        'reason',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'start_at' => 'datetime',
            'end_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    public function overlapsDate(Carbon $date): bool
    {
        $startOfDay = $date->copy()->startOfDay();
        $endOfDay = $date->copy()->endOfDay();

        return $this->start_at->lt($endOfDay) && $this->end_at->gt($startOfDay);
    }
}
