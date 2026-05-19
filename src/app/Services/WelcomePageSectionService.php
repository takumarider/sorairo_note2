<?php

namespace App\Services;

use App\Models\WelcomePageSection;
use Illuminate\Support\Collection;

class WelcomePageSectionService
{
    public function getVisibleSections(int $pageId = 1): Collection
    {
        return WelcomePageSection::query()
            ->forPage($pageId)
            ->visible()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();
    }

    public function hasVisibleSections(int $pageId = 1): bool
    {
        return WelcomePageSection::query()
            ->forPage($pageId)
            ->visible()
            ->exists();
    }

    public function normalizeType(?string $type): string
    {
        if (WelcomePageSection::isAllowedType($type)) {
            return (string) $type;
        }

        return WelcomePageSection::TYPE_TEXT;
    }
}
