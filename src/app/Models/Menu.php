<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    /** @use HasFactory<\Database\Factories\MenuFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'price',
        'duration',
        'image_path',
        'is_event',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_event' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function scopeEvents($query)
    {
        return $query->where('is_event', true);
    }

    public function scopeTreatments($query)
    {
        return $query->where('is_event', false);
    }

    public function slots()
    {
        return $this->hasMany(Slot::class);
    }

    public function reservations()
    {
        return $this->hasMany(Reservation::class);
    }

    public function options()
    {
        return $this->hasMany(MenuOption::class);
    }

    public function availableSlots()
    {
        $query = $this->slots()
            ->where('date', '>=', now()->toDateString())
            ->orderBy('date')
            ->orderBy('start_time');

        if ($this->is_event) {
            return $query->whereNotNull('capacity');
        }

        return $query->where('is_reserved', false);
    }
}
