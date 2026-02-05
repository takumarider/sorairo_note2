<?php

namespace App\Filament\Widgets;

use App\Models\Reservation;
use App\Models\Slot;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('総予約数', Reservation::count())
                ->description('すべての予約')
                ->descriptionIcon('heroicon-m-ticket')
                ->color('success'),
            
            Stat::make('今月の予約', Reservation::whereMonth('created_at', now()->month)->count())
                ->description(now()->format('Y年m月'))
                ->descriptionIcon('heroicon-m-calendar')
                ->color('primary'),
            
            Stat::make('確定済み', Reservation::where('status', 'confirmed')->count())
                ->description('確定している予約')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('info'),
            
            Stat::make('キャンセル', Reservation::where('status', 'canceled')->count())
                ->description('キャンセルされた予約')
                ->descriptionIcon('heroicon-m-x-circle')
                ->color('danger'),
            
            Stat::make('登録ユーザー', User::count())
                ->description('総ユーザー数')
                ->descriptionIcon('heroicon-m-users')
                ->color('warning'),
            
            Stat::make('空きスロット', Slot::where('is_reserved', false)->where('date', '>=', now())->count())
                ->description('予約可能な時間枠')
                ->descriptionIcon('heroicon-m-clock')
                ->color('success'),
        ];
    }
}
