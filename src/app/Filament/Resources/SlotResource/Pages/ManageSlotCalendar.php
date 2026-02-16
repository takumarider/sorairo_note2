<?php

namespace App\Filament\Resources\SlotResource\Pages;

use App\Filament\Resources\SlotResource;
use App\Models\Menu;
use App\Models\Slot;
use Carbon\Carbon;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TimePicker;
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

    public function mount(): void
    {
        $this->form->fill([
            'menu_id' => Menu::query()->value('id'),
            'business_date' => now()->toDateString(),
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
                    ->afterStateUpdated(fn () => $this->dispatch('slot-calendar-refresh')),
                DatePicker::make('business_date')
                    ->label('対象日')
                    ->required()
                    ->closeOnDateSelection()
                    ->afterStateUpdated(fn () => $this->dispatch('slot-calendar-refresh')),
                TimePicker::make('business_start')
                    ->label('営業開始時間')
                    ->seconds(false)
                    ->required(),
                TimePicker::make('business_end')
                    ->label('営業終了時間')
                    ->seconds(false)
                    ->required(),
            ])
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

        $businessDate = Carbon::parse($state['business_date'] ?? now()->toDateString());
        $start = Carbon::parse($businessDate->toDateString().' '.($state['business_start'] ?? '00:00'));
        $end = Carbon::parse($businessDate->toDateString().' '.($state['business_end'] ?? '00:00'));

        if ($start->gte($end)) {
            Notification::make()
                ->title('営業開始時間は終了時間より前に設定してください。')
                ->danger()
                ->send();

            return;
        }

        DB::transaction(function () use ($state, $businessDate, $start, $end, $intervalMinutes): void {
            Slot::where('menu_id', $state['menu_id'])
                ->whereDate('date', $businessDate->toDateString())
                ->where('is_reserved', false)
                ->delete();

            $cursor = clone $start;

            while ($cursor->lt($end)) {
                $next = (clone $cursor)->addMinutes($intervalMinutes);

                if ($next->gt($end)) {
                    break;
                }

                Slot::firstOrCreate(
                    [
                        'menu_id' => $state['menu_id'],
                        'date' => $businessDate->toDateString(),
                        'start_time' => $cursor->format('H:i:s'),
                    ],
                    [
                        'end_time' => $next->format('H:i:s'),
                        'is_reserved' => false,
                    ],
                );

                $cursor = $next;
            }
        });

        Notification::make()
            ->title("{$intervalMinutes}分ごとの時間枠を生成しました。")
            ->success()
            ->send();

        $this->dispatch('slot-calendar-refresh');
    }

    public function refreshCalendar(): void
    {
        $this->dispatch('slot-calendar-refresh');
    }

    public function getCalendarEvents(string $rangeStart, string $rangeEnd, ?int $menuId): array
    {
        if (! $menuId) {
            return [];
        }

        $startDate = Carbon::parse($rangeStart)->toDateString();
        $endDate = Carbon::parse($rangeEnd)->subDay()->toDateString();

        return Slot::with('menu')
            ->where('menu_id', $menuId)
            ->whereDate('date', '>=', now()->toDateString())
            ->whereBetween('date', [$startDate, $endDate])
            ->orderBy('date')
            ->orderBy('start_time')
            ->get()
            ->map(fn (Slot $slot) => [
                'id' => $slot->id,
                'title' => $slot->is_reserved ? '予約済み' : ($slot->menu->name ?? '時間枠'),
                'start' => $slot->date->format('Y-m-d').'T'.$slot->start_time->format('H:i:s'),
                'end' => $slot->date->format('Y-m-d').'T'.$slot->end_time->format('H:i:s'),
                'backgroundColor' => $slot->is_reserved ? '#93c5fd' : '#0ea5e9',
                'borderColor' => $slot->is_reserved ? '#60a5fa' : '#0284c7',
                'textColor' => '#082f49',
                'editable' => ! $slot->is_reserved,
                'durationEditable' => ! $slot->is_reserved,
                'extendedProps' => [
                    'is_reserved' => $slot->is_reserved,
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
}
