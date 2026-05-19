<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WelcomePageSection extends Model
{
    /** @use HasFactory<\Database\Factories\WelcomePageSectionFactory> */
    use HasFactory;

    public const TYPE_HERO = 'hero';

    public const TYPE_TEXT = 'text';

    public const TYPE_IMAGE_TEXT = 'image_text';

    public const TYPE_STORE_INFO = 'store_info';

    public const ALLOWED_TYPES = [
        self::TYPE_HERO,
        self::TYPE_TEXT,
        self::TYPE_IMAGE_TEXT,
        self::TYPE_STORE_INFO,
    ];

    protected $fillable = [
        'page_id',
        'type',
        'sort_order',
        'is_visible',
        'settings',
    ];

    protected function casts(): array
    {
        return [
            'is_visible' => 'boolean',
            'settings' => 'array',
        ];
    }

    public function scopeForPage(Builder $query, int $pageId = 1): Builder
    {
        return $query->where('page_id', $pageId);
    }

    public function scopeVisible(Builder $query): Builder
    {
        return $query->where('is_visible', true);
    }

    public static function isAllowedType(?string $type): bool
    {
        return in_array($type, self::ALLOWED_TYPES, true);
    }
}
