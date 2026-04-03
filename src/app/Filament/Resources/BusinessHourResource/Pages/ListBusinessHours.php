<?php

namespace App\Filament\Resources\BusinessHourResource\Pages;

use App\Filament\Resources\BusinessHourResource;
use App\Models\BusinessHour;
use Carbon\Carbon;
use Filament\Actions;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class ListBusinessHours extends ListRecords
{
    protected static string $resource = BusinessHourResource::class;

    protected static string $view = 'filament.resources.business-hour-resource.pages.list-business-hours';

    /** 表示中の月 (Y-m 形式) */
    public string $selectedMonth = '';

    public function mount(): void
    {
        parent::mount();
        $this->selectedMonth = now()->format('Y-m');
    }

    public function prevMonth(): void
    {
        $this->selectedMonth = Carbon::createFromFormat('Y-m', $this->selectedMonth)
            ->startOfMonth()
            ->subMonth()->format('Y-m');
    }

    public function nextMonth(): void
    {
        $this->selectedMonth = Carbon::createFromFormat('Y-m', $this->selectedMonth)
            ->startOfMonth()
            ->addMonth()->format('Y-m');
    }

    /**
     * 選択月の各日について、曜日デフォルト＋特定日上書きを解決したスケジュールを返す
     *
     * @return array{date: Carbon, setting: BusinessHour|null, source: string|null}[]
     */
    public function getMonthDays(): array
    {
        $month = Carbon::createFromFormat('Y-m', $this->selectedMonth ?: now()->format('Y-m'));
        $start = $month->clone()->startOfMonth();
        $end   = $month->clone()->endOfMonth();

        $days = [];
        for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
            $d       = $date->copy();
            $setting = BusinessHour::getSettingForDate($d);
            $source  = null;
            if ($setting) {
                $source = $setting->specific_date !== null ? 'specific' : 'weekly';
            }
            $days[] = [
                'date'    => $d,
                'setting' => $setting,
                'source'  => $source,
            ];
        }

        return $days;
    }

    /**
     * テーブルクエリを選択月に絞り込む
     * - 曜日デフォルト（specific_date IS NULL）は常に表示
     * - 特定日は選択月内のもののみ表示
     */
    protected function getTableQuery(): Builder
    {
        $month = Carbon::createFromFormat('Y-m', $this->selectedMonth ?: now()->format('Y-m'));

        return BusinessHour::query()
            ->where(function (Builder $q) use ($month): void {
                $q->whereNull('specific_date')
                    ->orWhereBetween('specific_date', [
                        $month->clone()->startOfMonth()->toDateString(),
                        $month->clone()->endOfMonth()->toDateString(),
                    ]);
            })
            ->orderByRaw('CASE WHEN specific_date IS NULL THEN 0 ELSE 1 END')
            ->orderBy('day_of_week')
            ->orderBy('specific_date');
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('setupTemplate')
                ->label('初期設定ガイド')
                ->icon('heroicon-m-sparkles')
                ->color('info')
                ->modalHeading('営業時間の初期設定')
                ->modalDescription('平日と土日の営業時間テンプレートをまとめて登録します。既存の曜日設定は上書きされます。')
                ->form([
                    TimePicker::make('weekday_open')
                        ->label('平日 開始')
                        ->required()
                        ->seconds(false)
                        ->default('09:00'),
                    TimePicker::make('weekday_close')
                        ->label('平日 終了')
                        ->required()
                        ->seconds(false)
                        ->default('18:00'),
                    TimePicker::make('saturday_open')
                        ->label('土曜 開始')
                        ->required()
                        ->seconds(false)
                        ->default('09:00'),
                    TimePicker::make('saturday_close')
                        ->label('土曜 終了')
                        ->required()
                        ->seconds(false)
                        ->default('18:00'),
                    Toggle::make('saturday_closed')
                        ->label('土曜を休業日にする')
                        ->default(false),
                    Toggle::make('sunday_closed')
                        ->label('日曜を休業日にする')
                        ->default(true),
                ])
                ->action(function (array $data): void {
                    DB::transaction(function () use ($data): void {
                        for ($day = 1; $day <= 5; $day++) {
                            BusinessHour::updateOrCreate(
                                [
                                    'day_of_week' => $day,
                                    'specific_date' => null,
                                ],
                                [
                                    'open_time' => $data['weekday_open'],
                                    'close_time' => $data['weekday_close'],
                                    'is_closed' => false,
                                ],
                            );
                        }

                        BusinessHour::updateOrCreate(
                            [
                                'day_of_week' => 6,
                                'specific_date' => null,
                            ],
                            [
                                'open_time' => $data['saturday_open'],
                                'close_time' => $data['saturday_close'],
                                'is_closed' => (bool) $data['saturday_closed'],
                            ],
                        );

                        BusinessHour::updateOrCreate(
                            [
                                'day_of_week' => 0,
                                'specific_date' => null,
                            ],
                            [
                                'open_time' => $data['saturday_open'],
                                'close_time' => $data['saturday_close'],
                                'is_closed' => (bool) $data['sunday_closed'],
                            ],
                        );
                    });

                    Notification::make()
                        ->title('営業時間の初期設定を保存しました。')
                        ->success()
                        ->send();
                }),
            Actions\CreateAction::make(),
        ];
    }
}
