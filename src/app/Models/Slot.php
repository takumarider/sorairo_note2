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
        'capacity',
        'is_reserved',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'start_time' => 'datetime:H:i',
            'end_time' => 'datetime:H:i',
            'capacity' => 'integer',
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

    public function reservations()
    {
        return $this->hasMany(Reservation::class);
    }

    public function confirmedReservations()
    {
        return $this->reservations()->where('status', 'confirmed');
    }

    public function confirmedCount(): int
    {
        if (array_key_exists('confirmed_reservations_count', $this->attributes)) {
            return (int) $this->attributes['confirmed_reservations_count'];
        }

        return $this->confirmedReservations()->count();
    }

    public function remainingCapacity(): ?int
    {
        if ($this->capacity === null) {
            return null;
        }

        return max($this->capacity - $this->confirmedCount(), 0);
    }

    public function isEventSlot(): bool
    {
        return (bool) ($this->menu?->is_event ?? false);
    }

    public function isAvailable(): bool
    {
        if ($this->date->lt(now()->startOfDay())) {
            return false;
        }

        if ($this->isEventSlot()) {
            return $this->capacity !== null && $this->remainingCapacity() > 0;
        }

        if ($this->is_reserved) {
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
