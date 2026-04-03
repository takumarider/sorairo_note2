<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MenuOption extends Model
{
    /** @use HasFactory<\Database\Factories\MenuOptionFactory> */
    use HasFactory;

    protected $fillable = [
        'menu_id',
        'name',
        'price',
        'duration',
        'image_path',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function menu()
    {
        return $this->belongsTo(Menu::class);
    }

    /**
     * 有効なオプションのみ取得するスコープ
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
