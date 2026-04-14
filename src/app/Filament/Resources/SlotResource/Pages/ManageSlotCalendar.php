<?php

namespace App\Filament\Resources\SlotResource\Pages;

use App\Filament\Resources\SlotResource;
use App\Models\BusinessHour;
use App\Models\Menu;
use App\Models\Slot;
use App\Models\TimeBlock;
use Carbon\Carbon;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Illuminate\Support\Facades\DB;

class ManageSlotCalendar extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string $resource = SlotResource::class;

    protected static string $view = 'filament.resources.slot-resource.pages.manage-slot-calendar';

    public ?array $data = [];

    public array $integrityIssues = [];

    public string $operationMode = 'slot';

    public string $blockReason = '';

    public function mount(): void
    {
        $this->form->fill([
            'menu_id' => Menu::query()->value('id'),
            'business_date' => now()->toDateString(),
            'business_end_date' => now()->toDateString(),
            'use_business_hours' => true,
            'business_start' => '09:00',
            'business_end' => '18:00',
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('menu_id')
                    ->label('メニュー')
                    ->options(fn () => Menu::pluck('name', 'id'))
                    ->searchable()
                    ->required()
                    ->columnSpanFull()
                    ->afterStateUpdated(fn () => $this->dispatch('slot-calendar-refresh')),
                Section::make('スロット自動生成設定')
                    ->collapsible()
                    ->collapsed()
                    ->schema([
                        DatePicker::make('business_date')
                            ->label('対象日')
                            ->required()
                            ->closeOnDateSelection()
                            ->afterStateUpdated(fn () => $this->dispatch('slot-calendar-refresh')),
                        DatePicker::make('business_end_date')
                            ->label('対象終了日')
                            ->required()
                            ->closeOnDateSelection(),
                        Toggle::make('use_business_hours')
                            ->label('営業時間マスタを優先する')
                            ->default(true)
                            ->helperText('有効時は営業時間マスタから開始・終了時刻を自動適用します。')
                            ->columnSpanFull(),
                        TimePicker::make('business_start')
                            ->label('営業開始時間')
                            ->seconds(false)
                            ->required()
                            ->disabled(fn (callable $get): bool => (bool) $get('use_business_hours')),
                        TimePicker::make('business_end')
                            ->label('営業終了時間')
                            ->seconds(false)
                            ->required()
                            ->disabled(fn (callable $get): bool => (bool) $get('use_business_hours')),
                    ])
                    ->columns(2),
            ])
            ->columns(1)
            ->statePath('data');
    }

    public function generateDailySlots(): void
    {
        $this->generateSlotsByInterval(30);
    }

    public function generateDailySlots60(): void
    {
        $this->generateSlotsByInterval(60);
    }

    public function generateDailySlots90(): void
    {
        $this->generateSlotsByInterval(90);
    }

    private function generateSlotsByInterval(int $intervalMinutes): void
    {
        $state = $this->form->getState();

        if (! ($state['menu_id'] ?? null)) {
            Notification::make()
                ->title('メニューを選択してください。')
                ->danger()
                ->send();

            return;
        }

        $startDate = Carbon::parse($state['business_date'] ?? now()->toDateString())->startOfDay();
        $endDate = Carbon::parse($state['business_end_date'] ?? $startDate->toDateString())->startOfDay();

        if ($startDate->gt($endDate)) {
            Notification::make()
                ->title('対象終了日は対象日以降に設定してください。')
                ->danger()
                ->send();

            return;
        }

        $generatedSlotCount = 0;
        $generatedDayCount = 0;

        DB::transaction(function () use ($state, $startDate, $endDate, $intervalMinutes, &$generatedSlotCount, &$generatedDayCount): void {
            for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
                [$rangeStart, $rangeEnd] = $this->resolveGenerationRange($state, $date);

                if (! $rangeStart || ! $rangeEnd) {
                    continue;
                }

                $generatedSlotCount += $this->regenerateUnreservedSlots(
                    $state['menu_id'],
                    $date,
                    $rangeStart,
                    $rangeEnd,
                    $intervalMinutes,
                );
                $generatedDayCount++;
            }
        });

        if ($generatedDayCount === 0) {
            Notification::make()
                ->title('対象期間に適用できる営業時間がありませんでした。')
                ->danger()
                ->send();

            return;
        }

        Notification::make()
            ->title("{$generatedDayCount}日分・{$generatedSlotCount}件の時間枠を生成しました。")
            ->success()
            ->send();

        $this->dispatch('slot-calendar-refresh');
    }

    private function resolveGenerationRange(array $state, Carbon $date): array
    {
        if ((bool) ($state['use_business_hours'] ?? false)) {
            $businessHour = BusinessHour::getForDate($date);

            if (! $businessHour) {
                return [null, null];
            }

            return [
                Carbon::parse($date->toDateString().' '.$businessHour->open_time),
                Carbon::parse($date->toDateString().' '.$businessHour->close_time),
            ];
        }

        $rangeStart = Carbon::parse($date->toDateString().' '.($state['business_start'] ?? '00:00'));
        $rangeEnd = Carbon::parse($date->toDateString().' '.($state['business_end'] ?? '00:00'));

        if ($rangeStart->gte($rangeEnd)) {
            return [null, null];
        }

        return [$rangeStart, $rangeEnd];
    }

    private function regenerateUnreservedSlots(int $menuId, Carbon $date, Carbon $start, Carbon $end, int $intervalMinutes): int
    {
        Slot::where('menu_id', $menuId)
            ->whereDate('date', $date->toDateString())
            ->where('is_reserved', false)
            ->delete();

        $createdCount = 0;
        $cursor = clone $start;

        while ($cursor->lt($end)) {
            $next = (clone $cursor)->addMinutes($intervalMinutes);

            if ($next->gt($end)) {
                break;
            }

            Slot::create([
                'menu_id' => $menuId,
                'date' => $date->toDateString(),
                'start_time' => $cursor->format('H:i:s'),
                'end_time' => $next->format('H:i:s'),
                'is_reserved' => false,
            ]);

            $createdCount++;
            $cursor = $next;
        }

        return $createdCount;
    }

    public function runIntegrityCheck(): void
    {
        $state = $this->form->getState();

        if (! ($state['menu_id'] ?? null)) {
            Notification::make()
                ->title('メニューを選択してください。')
                ->danger()
                ->send();

            return;
        }

        $startDate = Carbon::parse($state['business_date'] ?? now()->toDateString())->startOfDay();
        $endDate = Carbon::parse($state['business_end_date'] ?? $startDate->toDateString())->startOfDay();

        $issues = [];
        $slots = Slot::where('menu_id', $state['menu_id'])
            ->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])
            ->orderBy('date')
            ->orderBy('start_time')
            ->get();

        foreach ($slots as $slot) {
            $reasons = [];
            $setting = BusinessHour::getSettingForDate($slot->date->copy());

            $slotStart = Carbon::parse($slot->date->toDateString().' '.$slot->start_time->format('H:i:s'), 'Asia/Tokyo');
            $slotEnd = Carbon::parse($slot->date->toDateString().' '.$slot->end_time->format('H:i:s'), 'Asia/Tokyo');

            if (! $setting) {
                $reasons[] = '営業時間未設定';
            } elseif ($setting->is_closed) {
                $reasons[] = '休業日に時間枠あり';
            } else {
                $open = Carbon::parse($slot->date->toDateString().' '.$setting->open_time);
                $close = Carbon::parse($slot->date->toDateString().' '.$setting->close_time);

                if ($slotStart->lt($open) || $slotEnd->gt($close)) {
                    $reasons[] = '営業時間外';
                }
            }

            if ($slot->start_time->format('H:i:s') >= $slot->end_time->format('H:i:s')) {
                $reasons[] = '開始終了時刻が不正';
            }

            if ($slot->start_time->diffInMinutes($slot->end_time) % 30 !== 0) {
                $reasons[] = '30分単位以外';
            }

            $hasBlockConflict = TimeBlock::query()
                ->where('is_active', true)
                ->where('start_at', '<', $slotEnd)
                ->where('end_at', '>', $slotStart)
                ->exists();

            if ($hasBlockConflict) {
                $reasons[] = '時間帯ブロックと重複';
            }

            if ($reasons !== []) {
                $issues[] = [
                    'slot_id' => $slot->id,
                    'date' => $slot->date->format('Y-m-d'),
                    'start' => $slot->start_time->format('H:i'),
                    'end' => $slot->end_time->format('H:i'),
                    'is_reserved' => $slot->is_reserved,
                    'reasons' => $reasons,
                ];
            }
        }

        $this->integrityIssues = $issues;

        Notification::make()
            ->title($issues === [] ? '整合性の問題はありませんでした。' : count($issues).'件の問題を検出しました。')
            ->success()
            ->send();
    }

    public function deleteInvalidUnreservedSlots(): void
    {
        $targetIds = collect($this->integrityIssues)
            ->filter(fn (array $issue): bool => ! $issue['is_reserved'])
            ->pluck('slot_id')
            ->all();

        if ($targetIds === []) {
            Notification::make()
                ->title('削除できる未予約の不整合時間枠はありません。')
                ->danger()
                ->send();

            return;
        }

        Slot::whereIn('id', $targetIds)->delete();
        $this->runIntegrityCheck();
        $this->dispatch('slot-calendar-refresh');

        Notification::make()
            ->title(count($targetIds).'件の未予約時間枠を削除しました。')
            ->success()
            ->send();
    }

    public function refreshCalendar(): void
    {
        $this->dispatch('slot-calendar-refresh');
    }

    public function setOperationMode(string $mode): void
    {
        if (! in_array($mode, ['slot', 'block'], true)) {
            return;
        }

        $this->operationMode = $mode;
    }

    public function getCalendarEvents(string $rangeStart, string $rangeEnd, ?int $menuId): array
    {
        if (! $menuId) {
            return [];
        }

        $startDate = Carbon::parse($rangeStart, 'Asia/Tokyo')->toDateString();
        $endDate = Carbon::parse($rangeEnd, 'Asia/Tokyo')->subDay()->toDateString();

        $businessHourEvents = $this->getBusinessHourBackgroundEvents($startDate, $endDate);
        $blockEvents = $this->getTimeBlockEvents($startDate, $endDate);

        $slotEvents = Slot::with('menu')
            ->where('menu_id', $menuId)
            ->whereDate('date', '>=', now()->toDateString())
            ->whereBetween('date', [$startDate, $endDate])
            ->orderBy('date')
            ->orderBy('start_time')
            ->get()
            ->map(fn (Slot $slot) => [
                'id' => 'slot-'.$slot->id,
                'title' => $slot->is_reserved ? '予約済み' : ($slot->menu->name ?? '時間枠'),
                'start' => $slot->date->format('Y-m-d').'T'.$slot->start_time->format('H:i:s'),
                'end' => $slot->date->format('Y-m-d').'T'.$slot->end_time->format('H:i:s'),
                'backgroundColor' => $slot->is_reserved ? '#93c5fd' : '#0ea5e9',
                'borderColor' => $slot->is_reserved ? '#60a5fa' : '#0284c7',
                'textColor' => '#082f49',
                'editable' => ! $slot->is_reserved,
                'durationEditable' => ! $slot->is_reserved,
                'extendedProps' => [
                    'type' => 'slot',
                    'slot_id' => $slot->id,
                    'is_reserved' => $slot->is_reserved,
                ],
            ])
            ->all();

        return array_merge($businessHourEvents, $blockEvents, $slotEvents);
    }

    private function getBusinessHourBackgroundEvents(string $startDate, string $endDate): array
    {
        $events = [];

        $cursor = Carbon::parse($startDate, 'Asia/Tokyo')->startOfDay();
        $end = Carbon::parse($endDate, 'Asia/Tokyo')->startOfDay();

        while ($cursor->lte($end)) {
            $setting = BusinessHour::getSettingForDate($cursor);

            if ($setting && ! $setting->is_closed) {
                $open = Carbon::parse($setting->open_time)->format('H:i:s');
                $close = Carbon::parse($setting->close_time)->format('H:i:s');

                $events[] = [
                    'id' => 'business-hour-'.$cursor->format('Ymd'),
                    'title' => '営業時間 '.substr($open, 0, 5).'-'.substr($close, 0, 5),
                    'start' => $cursor->format('Y-m-d').'T'.$open,
                    'end' => $cursor->format('Y-m-d').'T'.$close,
                    'display' => 'background',
                    'backgroundColor' => 'rgba(14, 165, 233, 0.12)',
                    'borderColor' => 'rgba(14, 165, 233, 0.2)',
                    'editable' => false,
                    'durationEditable' => false,
                    'extendedProps' => [
                        'type' => 'business_hour',
                    ],
                ];
            }

            $cursor->addDay();
        }

        return $events;
    }

    private function getTimeBlockEvents(string $startDate, string $endDate): array
    {
        $start = Carbon::parse($startDate, 'Asia/Tokyo')->startOfDay();
        $end = Carbon::parse($endDate, 'Asia/Tokyo')->endOfDay();

        return TimeBlock::query()
            ->where('is_active', true)
            ->where('start_at', '<', $end)
            ->where('end_at', '>', $start)
            ->orderBy('start_at')
            ->get()
            ->map(fn (TimeBlock $block) => [
                'id' => 'block-'.$block->id,
                'title' => $block->reason ?: '時間帯ブロック',
                'start' => $block->start_at->format('Y-m-d\\TH:i:s'),
                'end' => $block->end_at->format('Y-m-d\\TH:i:s'),
                'backgroundColor' => '#ef4444',
                'borderColor' => '#dc2626',
                'textColor' => '#ffffff',
                'editable' => true,
                'durationEditable' => true,
                'extendedProps' => [
                    'type' => 'block',
                    'block_id' => $block->id,
                ],
            ])
            ->all();
    }

    public function createSlotFromCalendar(int $menuId, string $start, string $end): void
    {
        $startAt = Carbon::parse($start);
        $endAt = Carbon::parse($end);

        if ($startAt->gte($endAt)) {
            Notification::make()
                ->title('開始時間は終了時間より前に設定してください。')
                ->danger()
                ->send();

            return;
        }

        if ($startAt->diffInMinutes($endAt) % 30 !== 0) {
            Notification::make()
                ->title('30分単位で時間枠を作成してください。')
                ->danger()
                ->send();

            return;
        }

        $date = $startAt->toDateString();
        $startTime = $startAt->format('H:i:s');
        $endTime = $endAt->format('H:i:s');

        if (Slot::where('menu_id', $menuId)->whereDate('date', $date)->where('start_time', $startTime)->exists()) {
            Notification::make()
                ->title('同じ開始時間の枠が既に存在します。')
                ->danger()
                ->send();

            return;
        }

        Slot::create([
            'menu_id' => $menuId,
            'date' => $date,
            'start_time' => $startTime,
            'end_time' => $endTime,
            'is_reserved' => false,
        ]);

        Notification::make()
            ->title('時間枠を作成しました。')
            ->success()
            ->send();

        $this->dispatch('slot-calendar-refresh');
    }

    public function updateSlotFromCalendar(int $slotId, string $start, string $end): void
    {
        $slot = Slot::find($slotId);

        if (! $slot) {
            Notification::make()
                ->title('時間枠が見つかりませんでした。')
                ->danger()
                ->send();

            return;
        }

        if ($slot->is_reserved) {
            Notification::make()
                ->title('予約済みの時間枠は編集できません。')
                ->danger()
                ->send();

            $this->dispatch('slot-calendar-refresh');

            return;
        }

        $startAt = Carbon::parse($start);
        $endAt = Carbon::parse($end);

        if ($startAt->gte($endAt) || $startAt->diffInMinutes($endAt) % 30 !== 0) {
            Notification::make()
                ->title('30分単位で正しい時間範囲を指定してください。')
                ->danger()
                ->send();

            $this->dispatch('slot-calendar-refresh');

            return;
        }

        $date = $startAt->toDateString();
        $startTime = $startAt->format('H:i:s');

        $duplicate = Slot::where('menu_id', $slot->menu_id)
            ->whereDate('date', $date)
            ->where('start_time', $startTime)
            ->where('id', '!=', $slot->id)
            ->exists();

        if ($duplicate) {
            Notification::make()
                ->title('同じ時間の枠が既に存在します。')
                ->danger()
                ->send();

            $this->dispatch('slot-calendar-refresh');

            return;
        }

        $slot->update([
            'date' => $date,
            'start_time' => $startTime,
            'end_time' => $endAt->format('H:i:s'),
        ]);

        Notification::make()
            ->title('時間枠を更新しました。')
            ->success()
            ->send();

        $this->dispatch('slot-calendar-refresh');
    }

    public function deleteSlotFromCalendar(int $slotId): void
    {
        $slot = Slot::find($slotId);

        if (! $slot) {
            Notification::make()
                ->title('時間枠が見つかりませんでした。')
                ->danger()
                ->send();

            return;
        }

        if ($slot->is_reserved) {
            Notification::make()
                ->title('予約済みの時間枠は削除できません。')
                ->danger()
                ->send();

            return;
        }

        $slot->delete();

        Notification::make()
            ->title('時間枠を削除しました。')
            ->success()
            ->send();

        $this->dispatch('slot-calendar-refresh');
    }

    public function createBlockFromCalendar(string $start, string $end): void
    {
        $startAt = Carbon::parse($start, 'Asia/Tokyo');
        $endAt = Carbon::parse($end, 'Asia/Tokyo');

        if ($startAt->gte($endAt) || $startAt->diffInMinutes($endAt) % 30 !== 0) {
            Notification::make()
                ->title('30分単位で正しい時間範囲を指定してください。')
                ->danger()
                ->send();

            return;
        }

        TimeBlock::create([
            'start_at' => $startAt,
            'end_at' => $endAt,
            'reason' => filled($this->blockReason) ? $this->blockReason : null,
            'is_active' => true,
        ]);

        Notification::make()
            ->title('時間帯ブロックを作成しました。')
            ->success()
            ->send();

        $this->dispatch('slot-calendar-refresh');
    }

    public function deleteBlockFromCalendar(int $blockId): void
    {
        $block = TimeBlock::find($blockId);

        if (! $block) {
            Notification::make()
                ->title('時間帯ブロックが見つかりませんでした。')
                ->danger()
                ->send();

            return;
        }

        $block->delete();

        Notification::make()
            ->title('時間帯ブロックを削除しました。')
            ->success()
            ->send();

        $this->dispatch('slot-calendar-refresh');
    }

    public function updateBlockFromCalendar(int $blockId, string $start, string $end): void
    {
        $block = TimeBlock::find($blockId);

        if (! $block) {
            Notification::make()
                ->title('時間帯ブロックが見つかりませんでした。')
                ->danger()
                ->send();

            $this->dispatch('slot-calendar-refresh');

            return;
        }

        $startAt = Carbon::parse($start, 'Asia/Tokyo');
        $endAt = Carbon::parse($end, 'Asia/Tokyo');

        if ($startAt->gte($endAt) || $startAt->diffInMinutes($endAt) % 30 !== 0) {
            Notification::make()
                ->title('30分単位で正しい時間範囲を指定してください。')
                ->danger()
                ->send();

            $this->dispatch('slot-calendar-refresh');

            return;
        }

        $block->update([
            'start_at' => $startAt,
            'end_at' => $endAt,
        ]);

        Notification::make()
            ->title('時間帯ブロックを更新しました。')
            ->success()
            ->send();

        $this->dispatch('slot-calendar-refresh');
    }
}
