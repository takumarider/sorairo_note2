<?php

namespace App\Filament\Resources\MenuResource\Pages;

use App\Filament\Resources\MenuResource;
use App\Models\Menu;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListMenus extends ListRecords
{
    protected static string $resource = MenuResource::class;

    public function getTabs(): array
    {
        return [
            'treatments' => Tab::make('通常メニュー')
                ->badge(Menu::query()->treatments()->count())
                ->modifyQueryUsing(fn (Builder $query) => $query->treatments()),
            'events' => Tab::make('イベント')
                ->badge(Menu::query()->events()->count())
                ->modifyQueryUsing(fn (Builder $query) => $query->events()),
        ];
    }

    public function getDefaultActiveTab(): string | int | null
    {
        return 'treatments';
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
