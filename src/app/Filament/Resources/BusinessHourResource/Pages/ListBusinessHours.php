<?php

namespace App\Filament\Resources\BusinessHourResource\Pages;

use App\Filament\Resources\BusinessHourResource;
use App\Models\BusinessHour;
use App\Models\ReservationPublicationMonth;
use Carbon\Carbon;
use Filament\Actions;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Throwable;

class ListBusinessHours extends ListRecords
{
    protected static string $resource = BusinessHourResource::class;

    protected static string $view = 'filament.resources.business-hour-resource.pages.list-business-hours';

    /** 表示中の月 (Y-m 形式) */
    public string $selectedMonth = '';

    /** 表示中の月の公開設定（1:公開, 0:非公開） */
    public string $monthPublication = '0';

    public string $calendarModalMode = 'create';

    public ?string $calendarModalDate = null;

    public ?int $calendarModalBusinessHourId = null;

    public string $calendarModalOpenTime = '09:00';

    public string $calendarModalCloseTime = '18:00';

    public bool $calendarModalIsClosed = false;

    public function mount(): void
    {
        parent::mount();
        $this->selectedMonth = now()->format('Y-m');
        $this->syncMonthPublication();
    }

    public function prevMonth(): void
    {
        $this->selectedMonth = Carbon::createFromFormat('Y-m', $this->selectedMonth)
            ->startOfMonth()
            ->subMonth()->format('Y-m');
        $this->syncMonthPublication();
        $this->dispatch('business-hour-calendar-month-updated', month: $this->selectedMonth);
    }

    public function nextMonth(): void
    {
        $this->selectedMonth = Carbon::createFromFormat('Y-m', $this->selectedMonth)
            ->startOfMonth()
            ->addMonth()->format('Y-m');
        $this->syncMonthPublication();
        $this->dispatch('business-hour-calendar-month-updated', month: $this->selectedMonth);
    }

    public function saveMonthPublication(): void
    {
        ReservationPublicationMonth::query()->updateOrCreate(
            ['year_month' => $this->selectedMonth],
            ['is_published' => $this->monthPublication === '1']
        );

        Notification::make()
            ->title($this->monthPublication === '1'
                ? '選択中の月を公開しました。'
                : '選択中の月を非公開にしました。')
            ->success()
            ->send();
    }

    private function syncMonthPublication(): void
    {
        $this->monthPublication = ReservationPublicationMonth::isPublishedYearMonth($this->selectedMonth)
            ? '1'
            : '0';
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
        $end = $month->clone()->endOfMonth();

        $days = [];
        for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
            $d = $date->copy();
            $setting = BusinessHour::getSettingForDate($d);
            $source = null;
            if ($setting) {
                $source = $setting->specific_date !== null ? 'specific' : 'weekly';
            }
            $days[] = [
                'date' => $d,
                'setting' => $setting,
                'source' => $source,
            ];
        }

        return $days;
    }

    public function getCalendarEvents(string $rangeStart, string $rangeEnd): array
    {
        $start = Carbon::parse($rangeStart, 'Asia/Tokyo')->startOfDay();
        $end = Carbon::parse($rangeEnd, 'Asia/Tokyo')->subDay()->startOfDay();

        $events = [];

        for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
            $setting = BusinessHour::getSettingForDate($date);

            if ($setting === null) {
                $events[] = [
                    'id' => 'business-hour-unset-'.$date->format('Ymd'),
                    'title' => '未設定',
                    'start' => $date->toDateString(),
                    'allDay' => true,
                    'backgroundColor' => '#e2e8f0',
                    'borderColor' => '#cbd5e1',
                    'textColor' => '#475569',
                    'editable' => false,
                    'durationEditable' => false,
                    'extendedProps' => [
                        'source' => 'unset',
                        'date' => $date->toDateString(),
                    ],
                ];

                continue;
            }

            $isSpecific = $setting->specific_date !== null;

            if ($setting->is_closed) {
                $events[] = [
                    'id' => 'business-hour-closed-'.$date->format('Ymd'),
                    'title' => $isSpecific ? '休業（特定日）' : '休業',
                    'start' => $date->toDateString(),
                    'allDay' => true,
                    'backgroundColor' => $isSpecific ? '#fb7185' : '#fda4af',
                    'borderColor' => '#e11d48',
                    'textColor' => '#881337',
                    'editable' => false,
                    'durationEditable' => false,
                    'extendedProps' => [
                        'source' => $isSpecific ? 'specific' : 'weekly',
                        'date' => $date->toDateString(),
                        'business_hour_id' => $isSpecific ? $setting->id : null,
                        'is_closed' => true,
                    ],
                ];

                continue;
            }

            $open = Carbon::parse($setting->open_time)->format('H:i:s');
            $close = Carbon::parse($setting->close_time)->format('H:i:s');

            $events[] = [
                'id' => 'business-hour-open-'.$date->format('Ymd'),
                'title' => ($isSpecific ? '特定日 ' : '営業 ').substr($open, 0, 5).' - '.substr($close, 0, 5),
                'start' => $date->format('Y-m-d').'T'.$open,
                'end' => $date->format('Y-m-d').'T'.$close,
                'backgroundColor' => $isSpecific ? '#8b5cf6' : '#10b981',
                'borderColor' => $isSpecific ? '#7c3aed' : '#059669',
                'textColor' => '#ffffff',
                'editable' => false,
                'durationEditable' => false,
                'extendedProps' => [
                    'source' => $isSpecific ? 'specific' : 'weekly',
                    'date' => $date->toDateString(),
                    'business_hour_id' => $isSpecific ? $setting->id : null,
                    'is_closed' => false,
                ],
            ];
        }

        return $events;
    }

    public function createSpecificDateBusinessHour(string $date, ?string $openTime = null, ?string $closeTime = null, $isClosed = false): void
    {
        $isClosedFlag = $this->toBoolValue($isClosed);

        $dateCarbon = $this->parseDateInput($date);

        if (! $dateCarbon) {
            Notification::make()
                ->title('日付の形式が正しくありません。')
                ->danger()
                ->send();

            return;
        }

        $exists = BusinessHour::query()
            ->whereDate('specific_date', $dateCarbon->toDateString())
            ->exists();

        if ($exists) {
            Notification::make()
                ->title('この日付の特定日設定は既に存在します。')
                ->danger()
                ->send();

            return;
        }

        [$defaultOpen, $defaultClose] = $this->getDefaultOpenCloseForDate($dateCarbon);

        $open = $this->normalizeTimeInput($openTime) ?? $defaultOpen;
        $close = $this->normalizeTimeInput($closeTime) ?? $defaultClose;

        if (! $isClosedFlag && ! $this->isValidTimeRange($open, $close)) {
            Notification::make()
                ->title('営業時間は開始時刻より後の終了時刻を指定してください。')
                ->danger()
                ->send();

            return;
        }

        BusinessHour::query()->create([
            'day_of_week' => null,
            'specific_date' => $dateCarbon->toDateString(),
            'open_time' => $open,
            'close_time' => $close,
            'is_closed' => $isClosedFlag,
        ]);

        Notification::make()
            ->title('特定日の営業時間を作成しました。')
            ->success()
            ->send();

        $this->dispatch('business-hour-calendar-refresh');
    }

    public function updateSpecificDateBusinessHour(int $businessHourId, ?string $openTime = null, ?string $closeTime = null, $isClosed = false): void
    {
        $isClosedFlag = $this->toBoolValue($isClosed);

        $businessHour = BusinessHour::query()
            ->whereNotNull('specific_date')
            ->find($businessHourId);

        if (! $businessHour) {
            Notification::make()
                ->title('特定日設定が見つかりませんでした。')
                ->danger()
                ->send();

            $this->dispatch('business-hour-calendar-refresh');

            return;
        }

        $open = $this->normalizeTimeInput($openTime) ?? Carbon::parse($businessHour->open_time)->format('H:i:s');
        $close = $this->normalizeTimeInput($closeTime) ?? Carbon::parse($businessHour->close_time)->format('H:i:s');

        if (! $isClosedFlag && ! $this->isValidTimeRange($open, $close)) {
            Notification::make()
                ->title('営業時間は開始時刻より後の終了時刻を指定してください。')
                ->danger()
                ->send();

            $this->dispatch('business-hour-calendar-refresh');

            return;
        }

        $businessHour->update([
            'open_time' => $open,
            'close_time' => $close,
            'is_closed' => $isClosedFlag,
        ]);

        Notification::make()
            ->title('特定日の営業時間を更新しました。')
            ->success()
            ->send();

        $this->dispatch('business-hour-calendar-refresh');
    }

    public function deleteSpecificDateBusinessHour(int $businessHourId): void
    {
        $businessHour = BusinessHour::query()
            ->whereNotNull('specific_date')
            ->find($businessHourId);

        if (! $businessHour) {
            Notification::make()
                ->title('特定日設定が見つかりませんでした。')
                ->danger()
                ->send();

            $this->dispatch('business-hour-calendar-refresh');

            return;
        }

        $businessHour->delete();

        Notification::make()
            ->title('特定日の営業時間を削除しました。')
            ->success()
            ->send();

        $this->dispatch('business-hour-calendar-refresh');
    }

    public function openCreateSpecificDateModal(string $date): void
    {
        $dateCarbon = $this->parseDateInput($date);

        if (! $dateCarbon) {
            Notification::make()
                ->title('日付の形式が正しくありません。')
                ->danger()
                ->send();

            return;
        }

        $existing = BusinessHour::query()
            ->whereDate('specific_date', $dateCarbon->toDateString())
            ->first();

        if ($existing) {
            $this->openEditSpecificDateModal($existing->id);

            return;
        }

        [$defaultOpen, $defaultClose] = $this->getDefaultOpenCloseForDate($dateCarbon);

        $this->calendarModalMode = 'create';
        $this->calendarModalDate = $dateCarbon->toDateString();
        $this->calendarModalBusinessHourId = null;
        $this->calendarModalOpenTime = substr($defaultOpen, 0, 5);
        $this->calendarModalCloseTime = substr($defaultClose, 0, 5);
        $this->calendarModalIsClosed = false;

        $this->dispatch('open-modal', id: 'business-hour-calendar-editor');
    }

    public function openEditSpecificDateModal(int $businessHourId): void
    {
        $businessHour = BusinessHour::query()
            ->whereNotNull('specific_date')
            ->find($businessHourId);

        if (! $businessHour) {
            Notification::make()
                ->title('特定日設定が見つかりませんでした。')
                ->danger()
                ->send();

            return;
        }

        $this->calendarModalMode = 'edit';
        $this->calendarModalDate = $businessHour->specific_date?->format('Y-m-d');
        $this->calendarModalBusinessHourId = $businessHour->id;
        $this->calendarModalOpenTime = Carbon::parse($businessHour->open_time)->format('H:i');
        $this->calendarModalCloseTime = Carbon::parse($businessHour->close_time)->format('H:i');
        $this->calendarModalIsClosed = (bool) $businessHour->is_closed;

        $this->dispatch('open-modal', id: 'business-hour-calendar-editor');
    }

    public function submitCalendarModal(): void
    {
        if (! $this->calendarModalDate) {
            Notification::make()
                ->title('対象日が未設定です。')
                ->danger()
                ->send();

            return;
        }

        if ($this->calendarModalMode === 'edit' && $this->calendarModalBusinessHourId) {
            $this->updateSpecificDateBusinessHour(
                $this->calendarModalBusinessHourId,
                $this->calendarModalOpenTime,
                $this->calendarModalCloseTime,
                $this->calendarModalIsClosed,
            );
        } else {
            $this->createSpecificDateBusinessHour(
                $this->calendarModalDate,
                $this->calendarModalOpenTime,
                $this->calendarModalCloseTime,
                $this->calendarModalIsClosed,
            );
        }

        $this->dispatch('close-modal', id: 'business-hour-calendar-editor');
    }

    public function deleteFromCalendarModal(): void
    {
        if (! $this->calendarModalBusinessHourId) {
            return;
        }

        $this->deleteSpecificDateBusinessHour($this->calendarModalBusinessHourId);
        $this->dispatch('close-modal', id: 'business-hour-calendar-editor');
    }

    private function parseDateInput(string $date): ?Carbon
    {
        try {
            $parsed = Carbon::createFromFormat('Y-m-d', $date, 'Asia/Tokyo');

            if ($parsed->format('Y-m-d') !== $date) {
                return null;
            }

            return $parsed;
        } catch (Throwable) {
            return null;
        }
    }

    private function normalizeTimeInput(?string $time): ?string
    {
        if ($time === null || $time === '') {
            return null;
        }

        try {
            if (preg_match('/^\d{2}:\d{2}$/', $time) === 1) {
                return Carbon::createFromFormat('H:i', $time)->format('H:i:s');
            }

            if (preg_match('/^\d{2}:\d{2}:\d{2}$/', $time) === 1) {
                return Carbon::createFromFormat('H:i:s', $time)->format('H:i:s');
            }
        } catch (Throwable) {
            return null;
        }

        return null;
    }

    private function isValidTimeRange(string $openTime, string $closeTime): bool
    {
        $open = Carbon::createFromFormat('H:i:s', $openTime);
        $close = Carbon::createFromFormat('H:i:s', $closeTime);

        return $open->lt($close);
    }

    /** @return array{0: string, 1: string} */
    private function getDefaultOpenCloseForDate(Carbon $date): array
    {
        $setting = BusinessHour::getSettingForDate($date);

        if ($setting) {
            return [
                Carbon::parse($setting->open_time)->format('H:i:s'),
                Carbon::parse($setting->close_time)->format('H:i:s'),
            ];
        }

        return ['09:00:00', '18:00:00'];
    }

    private function toBoolValue(mixed $value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        if (is_int($value)) {
            return $value === 1;
        }

        if (is_string($value)) {
            return in_array(strtolower(trim($value)), ['1', 'true', 'yes', 'on'], true);
        }

        return false;
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
                ->label('営業時間マスタ設定')
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
            Actions\Action::make('calendar')
                ->label('営業カレンダー')
                ->icon('heroicon-m-calendar-days')
                ->color('gray')
                ->url(BusinessHourResource::getUrl('calendar')),
            Actions\CreateAction::make()
                ->label('特定日設定'),
        ];
    }
}
