<?php

namespace App\Filament\Resources\ReservationResource\Pages;

use App\Filament\Resources\ReservationResource;
use App\Models\Reservation;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Illuminate\Database\Eloquent\Builder;

class ManageReservationCalendar extends Page
{
    protected static string $resource = ReservationResource::class;

    protected static string $view = 'filament.resources.reservation-resource.pages.manage-reservation-calendar';

    protected static ?string $title = '予約カレンダー';

    public ?array $selectedReservation = null;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('list')
                ->label('一覧へ戻る')
                ->icon('heroicon-m-list-bullet')
                ->url(ReservationResource::getUrl()),
            Action::make('create')
                ->label('予約を追加')
                ->icon('heroicon-m-plus')
                ->color('primary')
                ->url(ReservationResource::getUrl('create')),
        ];
    }

    public function getCalendarEvents(string $start, string $end): array
    {
        $startDate = Carbon::parse($start, 'Asia/Tokyo')->startOfDay();
        $endDate = Carbon::parse($end, 'Asia/Tokyo')->startOfDay()->subDay();

        if ($endDate->lt($startDate)) {
            $endDate = $startDate->copy();
        }

        return Reservation::query()
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
            'extendedProps' => [
                'statusLabel' => $status['label'],
                'customerName' => $customerName,
                'menuName' => $menuName,
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

    protected function formatPrice(int $price): string
    {
        return '¥'.number_format($price);
    }
}
