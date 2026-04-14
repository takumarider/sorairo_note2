<?php

namespace App\Filament\Resources\BusinessHourResource\Pages;

use App\Filament\Resources\BusinessHourResource;
use Filament\Actions\Action;

class ManageBusinessHourCalendar extends ListBusinessHours
{
    protected static string $view = 'filament.resources.business-hour-resource.pages.manage-business-hour-calendar';

    protected static ?string $title = '営業時間カレンダー';

    protected function getHeaderActions(): array
    {
        return array_merge([
            Action::make('list')
                ->label('一覧へ戻る')
                ->icon('heroicon-m-list-bullet')
                ->url(BusinessHourResource::getUrl('index')),
        ], parent::getHeaderActions());
    }
}
