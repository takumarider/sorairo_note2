<?php

namespace App\Services;

use App\Models\BusinessHour;
use App\Models\Menu;
use App\Models\MenuOption;
use App\Models\Reservation;
use App\Models\ReservationPublicationMonth;
use App\Models\Slot;
use App\Models\TimeBlock;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Collection;

class AvailabilityService
{
    public function getMonthlyAvailabilitySummary(Menu $menu, array $optionIds, Carbon $month): array
    {
        $summary = [
            'configured_days' => 0,
            'open_days' => 0,
            'available_days' => 0,
        ];

        $start = $month->clone()->startOfMonth();
        $end = $month->clone()->endOfMonth();

        for ($date = $start; $date->lte($end); $date->addDay()) {
            if (! $this->isDatePublicForUsers($date)) {
                continue;
            }

            $setting = BusinessHour::getSettingForDate($date);

            if (! $setting) {
                continue;
            }

            $summary['configured_days']++;

            if ($setting->is_closed) {
                continue;
            }

            $summary['open_days']++;

            if (! empty($this->getAvailableTimesWithReason($menu, $optionIds, $date->toDateString())['times'])) {
                $summary['available_days']++;
            }
        }

        return $summary;
    }

    /**
     * 指定月の営業日ごとに空き状況を返す
     */
    public function getAvailableDates(Menu $menu, array $optionIds, Carbon $month): array
    {
        $result = [];
        $start = $month->clone()->startOfMonth();
        $end = $month->clone()->endOfMonth();

        for ($date = $start; $date->lte($end); $date->addDay()) {
            if (! $this->isDatePublicForUsers($date)) {
                $result[$date->toDateString()] = false;

                continue;
            }

            $result[$date->toDateString()] = ! empty(
                $this->getAvailableTimesWithReason($menu, $optionIds, $date->toDateString())['times']
            );
        }

        return $result;
    }

    public function getAvailableTimesWithReason(Menu $menu, array $optionIds, string $date): array
    {
        $dateCarbon = Carbon::createFromFormat('Y-m-d', $date)->startOfDay();
        $businessSetting = BusinessHour::getSettingForDate($dateCarbon);

        if (! $businessSetting) {
            return [
                'times' => [],
                'reason' => 'business_hours_not_set',
            ];
        }

        if ($businessSetting->is_closed) {
            return [
                'times' => [],
                'reason' => 'closed',
            ];
        }

        if ($menu->is_event) {
            return $this->getEventAvailableTimesWithReason($menu, $dateCarbon);
        }

        $totalDuration = $this->getTotalDuration($menu, $optionIds);
        $reservedRanges = $this->getReservedRanges($dateCarbon);
        $candidates = $this->buildCandidates($businessSetting, $totalDuration);

        if ($candidates === []) {
            return [
                'times' => [],
                'reason' => 'duration_too_long',
            ];
        }

        $available = [];
        $blockedByTimeBlock = false;
        foreach ($candidates as $candidate) {
            $startDateTime = Carbon::createFromFormat(
                'Y-m-d H:i',
                $dateCarbon->toDateString().' '.$candidate,
                'Asia/Tokyo'
            );
            $endDateTime = $startDateTime->clone()->addMinutes($totalDuration);

            $conflict = $this->hasConflict($startDateTime, $endDateTime, $reservedRanges);

            if (! $conflict) {
                $available[] = $candidate;
            } elseif ($this->isBlockedByTimeBlock($startDateTime, $endDateTime)) {
                $blockedByTimeBlock = true;
            }
        }

        return [
            'times' => $available,
            'reason' => empty($available)
                ? ($blockedByTimeBlock ? 'blocked' : 'fully_booked')
                : 'available',
            'slot_details' => [],
        ];
    }

    /**
     * 指定日付の利用可能な開始時刻リストを返す
     *
     * @param  array  $optionIds  MenuOption IDs
     * @param  string  $date  YYYY-MM-DD 形式
     * @return array ['10:00', '10:30', '11:00', ...]
     */
    public function getAvailableTimes(Menu $menu, array $optionIds, string $date): array
    {
        return $this->getAvailableTimesWithReason($menu, $optionIds, $date)['times'];
    }

    /**
     * 指定日で利用可能な時刻が存在するかを判定
     */
    private function hasAvailableTime(Menu $menu, array $optionIds, Carbon $date): bool
    {
        return ! empty($this->getAvailableTimesWithReason($menu, $optionIds, $date->toDateString())['times']);
    }

    /**
     * メニュー + 選択オプションの合計所要時間を計算
     */
    private function getTotalDuration(Menu $menu, array $optionIds): int
    {
        if ($menu->is_event) {
            return 0;
        }

        $duration = $menu->duration;

        if (! empty($optionIds)) {
            $optionDurations = MenuOption::whereIn('id', $optionIds)
                ->active()
                ->sum('duration');
            $duration += $optionDurations;
        }

        return $duration;
    }

    public function findReservableEventSlot(Menu $menu, string $date, string $startTime): ?Slot
    {
        if (! $menu->is_event) {
            return null;
        }

        $userId = Auth::id();
        if ($userId && $this->hasConfirmedEventReservationOnDate($menu, $date, (int) $userId)) {
            return null;
        }

        $slot = Slot::query()
            ->with('menu')
            ->withCount([
                'reservations as confirmed_reservations_count' => fn ($query) => $query->where('status', 'confirmed'),
            ])
            ->where('menu_id', $menu->id)
            ->whereDate('date', $date)
            ->where('start_time', $startTime)
            ->first();

        if (! $slot || ! $slot->isAvailable()) {
            return null;
        }

        return $slot;
    }

    private function getEventAvailableTimesWithReason(Menu $menu, Carbon $dateCarbon): array
    {
        $slots = Slot::query()
            ->with('menu')
            ->withCount([
                'reservations as confirmed_reservations_count' => fn ($query) => $query->where('status', 'confirmed'),
            ])
            ->where('menu_id', $menu->id)
            ->whereDate('date', $dateCarbon->toDateString())
            ->orderBy('start_time')
            ->get();

        if ($slots->isEmpty()) {
            return [
                'times' => [],
                'reason' => 'fully_booked',
                'slot_details' => [],
            ];
        }

        $userId = Auth::id();
        $userAlreadyReserved = $userId
            ? $this->hasConfirmedEventReservationOnDate($menu, $dateCarbon->toDateString(), (int) $userId)
            : false;

        $allSlotDetails = [];
        $availableTimes = [];

        foreach ($slots as $slot) {
            $time = $slot->start_time->format('H:i');

            if ($userAlreadyReserved) {
                $status = 'user_already_reserved';
            } elseif (! $slot->isAvailable()) {
                $status = 'fully_booked';
            } else {
                $status = 'available';
                $availableTimes[] = $time;
            }

            $allSlotDetails[$time] = [
                'id' => $slot->id,
                'capacity' => $slot->capacity,
                'confirmed_count' => $slot->confirmedCount(),
                'remaining_capacity' => $slot->remainingCapacity(),
                'end_time' => $slot->end_time->format('H:i'),
                'status' => $status,
            ];
        }

        if ($userAlreadyReserved) {
            $reason = 'user_already_reserved';
        } elseif (empty($availableTimes)) {
            $reason = 'fully_booked';
        } else {
            $reason = 'available';
        }

        return [
            'times' => $availableTimes,
            'reason' => $reason,
            'slot_details' => $allSlotDetails,
        ];
    }

    private function hasConfirmedEventReservationOnDate(Menu $menu, string $date, int $userId): bool
    {
        return Reservation::query()
            ->where('user_id', $userId)
            ->where('menu_id', $menu->id)
            ->whereDate('date', $date)
            ->where('status', 'confirmed')
            ->exists();
    }

    /**
     * 指定日の予約済み時間帯を取得
     */
    public function getReservedRanges(Carbon $date): Collection
    {
        $dateStr = $date->toDateString();
        $dayStart = $date->copy()->startOfDay();
        $dayEnd = $date->copy()->endOfDay();

        // 新方式：reservations テーブルのdate/start_time/end_time
        $newReservations = Reservation::whereDate('date', $dateStr)
            ->where('status', 'confirmed')
            ->get(['start_time', 'end_time']);

        // 旧方式：slots テーブルを経由した予約
        $oldReservations = Slot::whereDate('date', $dateStr)
            ->where('is_reserved', true)
            ->get(['start_time', 'end_time']);

        $timeBlocks = TimeBlock::query()
            ->where('is_active', true)
            ->where('start_at', '<', $dayEnd)
            ->where('end_at', '>', $dayStart)
            ->get(['start_at', 'end_at'])
            ->map(function (TimeBlock $block) use ($dayStart, $dayEnd): object {
                $startAt = $block->start_at->greaterThan($dayStart) ? $block->start_at : $dayStart;
                $endAt = $block->end_at->lessThan($dayEnd) ? $block->end_at : $dayEnd;

                return (object) [
                    'start_time' => $startAt->format('H:i'),
                    'end_time' => $endAt->format('H:i'),
                ];
            });

        return $newReservations->concat($oldReservations)->concat($timeBlocks);
    }

    /**
     * 営業時間内で30分刻みの開始時刻候補を生成
     *
     * @return array ['10:00', '10:30', '11:00', ...]
     */
    private function buildCandidates(BusinessHour $bh, int $durationMinutes): array
    {
        $candidates = [];

        // TimeオブジェクトをCarbonに変換
        $openTime = Carbon::parse('2000-01-01 '.$bh->open_time);
        $closeTime = Carbon::parse('2000-01-01 '.$bh->close_time);

        // 終了時刻がduration分を超えない最後の開始時刻を計算
        $lastStart = $closeTime->clone()->subMinutes($durationMinutes);

        $current = $openTime->clone();
        while ($current <= $lastStart) {
            $candidates[] = $current->format('H:i');
            $current->addMinutes(30);
        }

        return $candidates;
    }

    /**
     * 指定時間帯が既予約と重複しているかを判定
     */
    private function hasConflict(Carbon $start, Carbon $end, Collection $reservedRanges): bool
    {
        foreach ($reservedRanges as $reserved) {
            // reserved->start_time と reserved->end_time は Carbon オブジェクト または datetime:H:i でキャストされたもの
            $reservedStart = $reserved->start_time;
            $reservedEnd = $reserved->end_time;

            // Carbon オブジェクトの場合、時間だけを取得（日付部分は無視）
            if ($reservedStart instanceof Carbon) {
                $reservedStart = $reservedStart->format('H:i');
            }
            if ($reservedEnd instanceof Carbon) {
                $reservedEnd = $reservedEnd->format('H:i');
            }

            // 開始時刻と終了時刻を比較用に統一フォーマットで変換
            $startStr = $start->format('H:i');
            $endStr = $end->format('H:i');

            // 時刻文字列の比較で重複判定
            // 重複条件: !(end <= reservedStart || start >= reservedEnd)
            if (! ($endStr <= $reservedStart || $startStr >= $reservedEnd)) {
                return true;
            }
        }

        return false;
    }

    private function isDatePublicForUsers(Carbon $date): bool
    {
        return $this->isMonthPublicForUsers($date);
    }

    public function isMonthPublicForUsers(Carbon $month): bool
    {
        return ReservationPublicationMonth::isPublishedForMonth($month);
    }

    private function isBlockedByTimeBlock(Carbon $start, Carbon $end): bool
    {
        return TimeBlock::query()
            ->where('is_active', true)
            ->where('start_at', '<', $end)
            ->where('end_at', '>', $start)
            ->exists();
    }
}
