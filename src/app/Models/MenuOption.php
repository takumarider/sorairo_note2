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
            // 割引オプションを表現できるよう price/duration は負の値も許容する。
            'price' => 'integer',
            'duration' => 'integer',
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

    /**
     * 符号付きで表示する追加料金ラベル（例: +¥500 / -¥300）
     */
    public function priceLabel(): string
    {
        $sign = $this->price < 0 ? '-' : '+';

        return $sign.'¥'.number_format(abs($this->price));
    }

    /**
     * 符号付きで表示する追加所要時間ラベル（例: +30分 / -15分）
     */
    public function durationLabel(): string
    {
        $sign = $this->duration < 0 ? '-' : '+';

        return $sign.abs($this->duration).'分';
    }
}
