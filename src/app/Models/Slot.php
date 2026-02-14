<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Slot extends Model
{
    /** @use HasFactory<\Database\Factories\SlotFactory> */
    use HasFactory;

    protected $fillable = [
        'menu_id',
        'date',
        'start_time',
        'end_time',
        'is_reserved',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'start_time' => 'datetime:H:i',
            'end_time' => 'datetime:H:i',
            'is_reserved' => 'boolean',
        ];
    }

    public function menu()
    {
        return $this->belongsTo(Menu::class);
    }

    public function reservation()
    {
        return $this->hasOne(Reservation::class);
    }

    public function isAvailable(): bool
    {
        if ($this->is_reserved) {
            return false;
        }

        if ($this->date->lt(now()->startOfDay())) {
            return false;
        }

        return ! $this->hasOverlappingReservedSlot();
    }

    public function hasOverlappingReservedSlot(): bool
    {
        return self::query()
            ->whereDate('date', $this->date->toDateString())
            ->where('is_reserved', true)
            ->when(
                $this->exists,
                fn ($query) => $query->where('id', '!=', $this->id)
            )
            ->where('start_time', '<', $this->end_time->format('H:i:s'))
            ->where('end_time', '>', $this->start_time->format('H:i:s'))
            ->exists();
    }
}
