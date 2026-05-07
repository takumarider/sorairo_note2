<?php

namespace App\Filament\Resources\ReservationResource\Pages;

use App\Filament\Resources\ReservationResource;
use App\Models\Reservation;
use App\Models\Slot;
use App\Models\TimeBlock;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class ManageReservationCalendar extends Page
{
    protected static string $resource = ReservationResource::class;

    protected static string $view = 'filament.resources.reservation-resource.pages.manage-reservation-calendar';

    protected static ?string $title = '予約カレンダー';

    public ?array $selectedReservation = null;

    public ?array $selectedSlot = null;

    public string $operationMode = 'reservation';

    public string $blockReason = '';

    public ?string $pendingBlockStart = null;

    public ?string $pendingBlockEnd = null;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('list')
                ->label('一覧へ戻る')
                ->icon('heroicon-m-list-bullet')
                ->url(ReservationResource::getUrl()),
        ];
    }

    public function getCalendarEvents(string $start, string $end): array
    {
        $startDate = Carbon::parse($start, 'Asia/Tokyo')->startOfDay();
        $endDate = Carbon::parse($end, 'Asia/Tokyo')->startOfDay()->subDay();

        if ($endDate->lt($startDate)) {
            $endDate = $startDate->copy();
        }

        $reservationEvents = Reservation::query()
            ->with(['user', 'menu', 'options', 'slot'])
            ->where(function (Builder $query) use ($startDate, $endDate): void {
                $query
                    ->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])
                    ->orWhereHas('slot', function (Builder $slotQuery) use ($startDate, $endDate): void {
                        $slotQuery->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()]);
                    });
            })
            ->get()
            ->map(fn (Reservation $reservation): ?array => $this->buildCalendarEvent($reservation))
            ->filter()
            ->sortBy('start')
            ->values()
            ->all();

        $slotEvents = $this->getEventSlotEvents($startDate, $endDate);

        $blockEvents = $this->getTimeBlockEvents($startDate, $endDate);

        return collect(array_merge($blockEvents, $reservationEvents, $slotEvents))
            ->sortBy('start')
            ->values()
            ->all();
    }

    public function setOperationMode(string $mode): void
    {
        if (! in_array($mode, ['reservation', 'block'], true)) {
            return;
        }

        $this->operationMode = $mode;
    }

    public function showBlockConfirmModal(string $start, string $end): void
    {
        $this->pendingBlockStart = $start;
        $this->pendingBlockEnd = $end;
        $this->dispatch('open-modal', id: 'block-create-confirm');
    }

    public function confirmCreateBlock(): void
    {
        if ($this->pendingBlockStart && $this->pendingBlockEnd) {
            $this->createBlockFromCalendar($this->pendingBlockStart, $this->pendingBlockEnd);
        }

        $this->pendingBlockStart = null;
        $this->pendingBlockEnd = null;
        $this->dispatch('close-modal', id: 'block-create-confirm');
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

        $this->dispatch('reservation-calendar-refetch');
    }

    public function updateBlockFromCalendar(int $blockId, string $start, string $end): void
    {
        $block = TimeBlock::find($blockId);

        if (! $block) {
            Notification::make()
                ->title('時間帯ブロックが見つかりませんでした。')
                ->danger()
                ->send();

            $this->dispatch('reservation-calendar-refetch');

            return;
        }

        $startAt = Carbon::parse($start, 'Asia/Tokyo');
        $endAt = Carbon::parse($end, 'Asia/Tokyo');

        if ($startAt->gte($endAt) || $startAt->diffInMinutes($endAt) % 30 !== 0) {
            Notification::make()
                ->title('30分単位で正しい時間範囲を指定してください。')
                ->danger()
                ->send();

            $this->dispatch('reservation-calendar-refetch');

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

        $this->dispatch('reservation-calendar-refetch');
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

        $this->dispatch('reservation-calendar-refetch');
    }

    public function openReservationModal(int $reservationId): void
    {
        $reservation = Reservation::query()
            ->with(['user', 'menu', 'options', 'slot'])
            ->find($reservationId);

        if (! $reservation) {
            Notification::make()
                ->title('予約情報を取得できませんでした。')
                ->danger()
                ->send();

            return;
        }

        $payload = $this->buildReservationPayload($reservation);

        if (! $payload) {
            Notification::make()
                ->title('予約日時を解決できないため詳細を表示できません。')
                ->danger()
                ->send();

            return;
        }

        $this->selectedReservation = $payload;
        $this->dispatch('open-modal', id: 'reservation-calendar-detail');
    }

    public function openSlotModal(int $slotId): void
    {
        $slot = Slot::query()
            ->with('menu')
            ->withCount([
                'reservations as confirmed_reservations_count' => fn (Builder $query) => $query->where('status', 'confirmed'),
            ])
            ->find($slotId);

        if (! $slot) {
            Notification::make()
                ->title('イベント枠情報を取得できませんでした。')
                ->danger()
                ->send();

            return;
        }

        $payload = $this->buildSlotPayload($slot);

        if (! $payload) {
            Notification::make()
                ->title('イベント枠日時を解決できません。')
                ->danger()
                ->send();

            return;
        }

        $this->selectedSlot = $payload;
        $this->dispatch('open-modal', id: 'reservation-calendar-slot-detail');
    }

    public function cancelSelectedReservation(): void
    {
        if (! $this->selectedReservation || ! isset($this->selectedReservation['id'])) {
            return;
        }

        $reservation = Reservation::query()->with(['user', 'menu', 'options', 'slot'])->find((int) $this->selectedReservation['id']);

        if (! $reservation) {
            Notification::make()
                ->title('予約情報を取得できませんでした。')
                ->danger()
                ->send();

            return;
        }

        if (! $reservation->canCancel()) {
            Notification::make()
                ->title($reservation->cancellationFailureReasonBy(Auth::user()))
                ->warning()
                ->send();

            return;
        }

        $reservation->cancel();

        $payload = $this->buildReservationPayload($reservation->fresh(['user', 'menu', 'options', 'slot']));
        if ($payload) {
            $this->selectedReservation = $payload;
        }

        $this->dispatch('reservation-calendar-refetch');

        Notification::make()
            ->title('予約をキャンセルしました。')
            ->success()
            ->send();
    }

    protected function buildCalendarEvent(Reservation $reservation): ?array
    {
        $startAt = $this->resolveReservationDateTime($reservation, 'start');
        $endAt = $this->resolveReservationDateTime($reservation, 'end');

        if (! $startAt || ! $endAt) {
            return null;
        }

        $status = $this->getStatusMeta($reservation->status);
        $customerName = $reservation->user?->name ?? '未設定';
        $menuName = $reservation->menu?->name ?? 'メニュー未設定';

        return [
            'id' => (string) $reservation->id,
            'title' => sprintf('%s %s / %s', $startAt->format('H:i'), $customerName, $menuName),
            'start' => $startAt->format('Y-m-d\TH:i:s'),
            'end' => $endAt->format('Y-m-d\TH:i:s'),
            'backgroundColor' => $status['background'],
            'borderColor' => $status['border'],
            'textColor' => $status['text'],
            'editable' => false,
            'durationEditable' => false,
            'extendedProps' => [
                'type' => 'reservation',
                'reservation_id' => $reservation->id,
                'statusLabel' => $status['label'],
                'customerName' => $customerName,
                'menuName' => $menuName,
            ],
        ];
    }

    protected function getEventSlotEvents(Carbon $startDate, Carbon $endDate): array
    {
        return Slot::query()
            ->with('menu')
            ->withCount([
                'reservations as confirmed_reservations_count' => fn (Builder $query) => $query->where('status', 'confirmed'),
            ])
            ->whereHas('menu', fn (Builder $query) => $query->where('is_event', true))
            ->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])
            ->orderBy('date')
            ->orderBy('start_time')
            ->get()
            ->map(fn (Slot $slot): ?array => $this->buildEventSlotCalendarEvent($slot))
            ->filter()
            ->values()
            ->all();
    }

    protected function buildEventSlotCalendarEvent(Slot $slot): ?array
    {
        $startAt = $this->resolveSlotDateTime($slot, 'start');
        $endAt = $this->resolveSlotDateTime($slot, 'end');

        if (! $startAt || ! $endAt) {
            return null;
        }

        $menuName = $slot->menu?->name ?? 'イベント枠';
        $remainingCapacity = $slot->remainingCapacity();
        $confirmedCount = $slot->confirmedCount();
        $isFull = $remainingCapacity !== null && $remainingCapacity <= 0;

        return [
            'id' => 'slot-'.$slot->id,
            'title' => sprintf('%s-%s %s（枠）', $startAt->format('H:i'), $endAt->format('H:i'), $menuName),
            'start' => $startAt->format('Y-m-d\TH:i:s'),
            'end' => $endAt->format('Y-m-d\TH:i:s'),
            'backgroundColor' => $isFull ? '#60a5fa' : '#0284c7',
            'borderColor' => $isFull ? '#3b82f6' : '#0369a1',
            'textColor' => '#f0f9ff',
            'editable' => false,
            'durationEditable' => false,
            'extendedProps' => [
                'type' => 'slot',
                'slot_id' => $slot->id,
                'menuName' => $menuName,
                'capacity' => $slot->capacity,
                'confirmed_count' => $confirmedCount,
                'remaining_capacity' => $remainingCapacity,
            ],
        ];
    }

    protected function buildReservationPayload(Reservation $reservation): ?array
    {
        $startAt = $this->resolveReservationDateTime($reservation, 'start');
        $endAt = $this->resolveReservationDateTime($reservation, 'end');

        if (! $startAt || ! $endAt) {
            return null;
        }

        $status = $this->getStatusMeta($reservation->status);
        $menuPrice = (int) ($reservation->menu?->price ?? 0);
        $menuDuration = (int) ($reservation->menu?->duration ?? 0);
        $optionTotalPrice = (int) $reservation->options->sum('price');
        $optionTotalDuration = (int) $reservation->options->sum('duration');

        return [
            'id' => $reservation->id,
            'number' => str_pad((string) $reservation->id, 6, '0', STR_PAD_LEFT),
            'customer_name' => $reservation->user?->name ?? '未設定',
            'customer_email' => $reservation->user?->email ?? '未設定',
            'menu_name' => $reservation->menu?->name ?? '未設定',
            'menu_price_label' => $this->formatPrice($menuPrice),
            'menu_duration_label' => $menuDuration > 0 ? $menuDuration.'分' : '未設定',
            'options' => $reservation->options
                ->map(fn ($option): array => [
                    'name' => $option->name,
                    'price_label' => $this->formatPrice((int) $option->price),
                    'duration_label' => ((int) $option->duration) > 0 ? ((int) $option->duration).'分' : '未設定',
                ])
                ->values()
                ->all(),
            'option_total_price_label' => $this->formatPrice($optionTotalPrice),
            'option_total_duration_label' => $optionTotalDuration > 0 ? $optionTotalDuration.'分' : '0分',
            'total_price_label' => $this->formatPrice($menuPrice + $optionTotalPrice),
            'total_duration_label' => ($menuDuration + $optionTotalDuration) > 0 ? ($menuDuration + $optionTotalDuration).'分' : '未設定',
            'date_label' => $startAt->locale('ja')->isoFormat('Y年M月D日(ddd)'),
            'time_label' => $startAt->format('H:i').' - '.$endAt->format('H:i'),
            'status_label' => $status['label'],
            'status_badge_class' => $status['badge'],
        ];
    }

    protected function buildSlotPayload(Slot $slot): ?array
    {
        $startAt = $this->resolveSlotDateTime($slot, 'start');
        $endAt = $this->resolveSlotDateTime($slot, 'end');

        if (! $startAt || ! $endAt) {
            return null;
        }

        $remainingCapacity = $slot->remainingCapacity();
        $confirmedCount = $slot->confirmedCount();
        $statusLabel = match (true) {
            $slot->capacity === null => '定員未設定',
            $remainingCapacity !== null && $remainingCapacity <= 0 => '満席',
            default => '受付中',
        };

        $statusBadgeClass = match (true) {
            $slot->capacity === null => 'bg-slate-100 text-slate-700 ring-slate-200',
            $remainingCapacity !== null && $remainingCapacity <= 0 => 'bg-blue-100 text-blue-700 ring-blue-200',
            default => 'bg-sky-100 text-sky-700 ring-sky-200',
        };

        return [
            'id' => $slot->id,
            'menu_name' => $slot->menu?->name ?? 'イベント枠',
            'date_label' => $startAt->locale('ja')->isoFormat('Y年M月D日(ddd)'),
            'time_label' => $startAt->format('H:i').' - '.$endAt->format('H:i'),
            'capacity_label' => $slot->capacity !== null ? $slot->capacity.'名' : '未設定',
            'confirmed_count_label' => $confirmedCount.'名',
            'remaining_capacity_label' => $remainingCapacity !== null ? $remainingCapacity.'名' : '未設定',
            'status_label' => $statusLabel,
            'status_badge_class' => $statusBadgeClass,
        ];
    }

    protected function resolveSlotDateTime(Slot $slot, string $edge): ?Carbon
    {
        $date = $slot->date;
        $time = $edge === 'start' ? $slot->start_time : $slot->end_time;

        if (! $date || ! $time) {
            return null;
        }

        return Carbon::createFromFormat(
            'Y-m-d H:i:s',
            $date->toDateString().' '.$time->format('H:i:s'),
            'Asia/Tokyo',
        );
    }

    protected function resolveReservationDateTime(Reservation $reservation, string $edge): ?Carbon
    {
        $date = $reservation->date ?? $reservation->slot?->date;
        $time = $edge === 'start'
            ? ($reservation->start_time ?? $reservation->slot?->start_time)
            : ($reservation->end_time ?? $reservation->slot?->end_time);

        if (! $date || ! $time) {
            return null;
        }

        return Carbon::createFromFormat(
            'Y-m-d H:i:s',
            $date->toDateString().' '.$time->format('H:i:s'),
            'Asia/Tokyo',
        );
    }

    protected function getStatusMeta(string $status): array
    {
        return match ($status) {
            'confirmed' => [
                'label' => '確定',
                'background' => '#0f766e',
                'border' => '#115e59',
                'text' => '#ecfeff',
                'badge' => 'bg-teal-50 text-teal-700 ring-teal-200',
            ],
            'completed' => [
                'label' => '完了',
                'background' => '#b45309',
                'border' => '#92400e',
                'text' => '#fffbeb',
                'badge' => 'bg-amber-50 text-amber-700 ring-amber-200',
            ],
            'canceled' => [
                'label' => 'キャンセル',
                'background' => '#be123c',
                'border' => '#9f1239',
                'text' => '#fff1f2',
                'badge' => 'bg-rose-50 text-rose-700 ring-rose-200',
            ],
            default => [
                'label' => $status,
                'background' => '#475569',
                'border' => '#334155',
                'text' => '#f8fafc',
                'badge' => 'bg-slate-100 text-slate-700 ring-slate-200',
            ],
        };
    }

    protected function getTimeBlockEvents(Carbon $startDate, Carbon $endDate): array
    {
        $start = $startDate->copy()->startOfDay();
        $end = $endDate->copy()->endOfDay();

        return TimeBlock::query()
            ->where('is_active', true)
            ->where('start_at', '<', $end)
            ->where('end_at', '>', $start)
            ->orderBy('start_at')
            ->get()
            ->map(fn (TimeBlock $block) => [
                'id' => 'block-'.$block->id,
                'title' => $block->reason ?: '時間帯ブロック',
                'start' => $block->start_at->format('Y-m-d\TH:i:s'),
                'end' => $block->end_at->format('Y-m-d\TH:i:s'),
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
            ->values()
            ->all();
    }

    protected function formatPrice(int $price): string
    {
        return '¥'.number_format($price);
    }
}
