<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    /** @use HasFactory<\Database\Factories\MenuFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'price',
        'price_max',
        'duration',
        'image_path',
        'is_event',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_event' => 'boolean',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
            'price_max' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Menu $menu): void {
            if ($menu->sort_order !== null) {
                return;
            }

            $maxSortOrder = static::query()
                ->where('is_event', (bool) $menu->is_event)
                ->max('sort_order');

            $menu->sort_order = ($maxSortOrder ?? 0) + 1;
        });

        static::updating(function (Menu $menu): void {
            if (! $menu->isDirty('is_event') || $menu->isDirty('sort_order')) {
                return;
            }

            $maxSortOrder = static::query()
                ->where('is_event', (bool) $menu->is_event)
                ->max('sort_order');

            $menu->sort_order = ($maxSortOrder ?? 0) + 1;
        });
    }

    public function scopeEvents(Builder $query): Builder
    {
        return $query->where('is_event', true);
    }

    public function scopeTreatments(Builder $query): Builder
    {
        return $query->where('is_event', false);
    }

    public function scopeOrderedForDisplay(Builder $query): Builder
    {
        return $query
            ->orderByRaw('sort_order IS NULL')
            ->orderBy('sort_order')
            ->orderBy('id');
    }

    public function scopeOrderedByTypeForDisplay(Builder $query): Builder
    {
        return $query
            ->orderBy('is_event')
            ->orderedForDisplay();
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
