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
        return ! $this->is_reserved && $this->date >= now()->toDateString();
    }
}
