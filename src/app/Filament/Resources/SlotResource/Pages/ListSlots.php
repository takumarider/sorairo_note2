<?php

namespace App\Filament\Resources\SlotResource\Pages;

use App\Filament\Resources\SlotHistoryResource;
use App\Filament\Resources\SlotResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSlots extends ListRecords
{
    protected static string $resource = SlotResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('calendar')
                ->label('カレンダーで管理')
                ->icon('heroicon-m-calendar')
                ->url(SlotResource::getUrl('calendar')),
            Actions\Action::make('history')
                ->label('履歴')
                ->icon('heroicon-m-archive-box')
                ->url(SlotHistoryResource::getUrl()),
            Actions\CreateAction::make(),
        ];
    }
}
