<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use App\Models\MenuOption;
use App\Models\Reservation;
use App\Models\ReservationPublicationMonth;
use App\Models\Slot;
use App\Models\User;
use App\Services\AvailabilityService;
use App\Services\NotificationService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ReservationController extends Controller
{
    private const MONTH_UNPUBLISHED_REASON = 'month_unpublished';

    public function __construct(
        private NotificationService $notificationService
    ) {}

    /**
     * カレンダー画面（月表示で空き有無を表示）
     */
    public function calendar(Request $request)
    {
        $validated = $request->validate([
            'menu_id' => 'required|exists:menus,id',
            'options' => 'nullable|array',
            'options.*' => 'exists:menu_options,id',
            'month' => 'nullable|date_format:Y-m',
        ]);

        $menuId = $validated['menu_id'];
        $optionIds = $validated['options'] ?? [];
        $month = ! empty($validated['month'])
            ? Carbon::createFromFormat('Y-m', $validated['month'], 'Asia/Tokyo')->startOfMonth()
            : now('Asia/Tokyo')->startOfMonth();
        $availabilityReason = null;

        if (! $this->isMonthVisibleToUsers($month)) {
            $fallbackMonth = $this->resolveFallbackPublishedMonth();

            if ($fallbackMonth && ! $fallbackMonth->isSameMonth($month)) {
                return redirect()->route('reservations.calendar', [
                    'menu_id' => $menuId,
                    'options' => $optionIds,
                    'month' => $fallbackMonth->format('Y-m'),
                ])->with('availability_reason', self::MONTH_UNPUBLISHED_REASON);
            }

            $availabilityReason = self::MONTH_UNPUBLISHED_REASON;
        }

        $menu = Menu::findOrFail($menuId);

        $options = ! empty($optionIds)
            ? $this->resolveOptions($menu, $optionIds)
            : collect();

        $availabilityService = new AvailabilityService;
        $availableDates = $availabilityService->getAvailableDates($menu, $optionIds, $month);
        $availabilitySummary = $availabilityService->getMonthlyAvailabilitySummary($menu, $optionIds, $month);

        // 合計所要時間・合計料金を計算
        $totalDuration = $menu->is_event ? 0 : $menu->duration;
        $totalPrice = $menu->price;
        foreach ($options as $option) {
            $totalDuration += $option->duration;
            $totalPrice += $option->price;
        }

        return view('reservations.calendar', [
            'menu' => $menu,
            'options' => $options,
            'optionIds' => $optionIds,
            'availableDates' => $availableDates,
            'availabilitySummary' => $availabilitySummary,
            'month' => $month,
            'totalDuration' => $totalDuration,
            'totalPrice' => $totalPrice,
            'canViewNextMonth' => $this->isMonthVisibleToUsers($month->clone()->addMonth()),
            'availabilityReason' => $availabilityReason,
        ]);
    }

    /**
     * 時刻選択画面（指定日の利用可能時刻を表示）
     */
    public function times(Request $request)
    {
        $request->validate([
            'menu_id' => 'required|exists:menus,id',
            'date' => 'required|date_format:Y-m-d|after_or_equal:today',
        ]);

        $menuId = $request->input('menu_id');
        $optionIds = $request->input('options', []);
        $date = $request->input('date');

        if (! $this->isDateReservableForUsers($date)) {
            return redirect()->route('reservations.calendar', [
                'menu_id' => $menuId,
                'options' => $optionIds,
                'month' => now('Asia/Tokyo')->format('Y-m'),
            ])->with('availability_reason', self::MONTH_UNPUBLISHED_REASON);
        }

        $menu = Menu::findOrFail($menuId);

        $options = ! empty($optionIds)
            ? $this->resolveOptions($menu, $optionIds)
            : collect();

        $availabilityService = new AvailabilityService;
        $availability = $availabilityService->getAvailableTimesWithReason($menu, $optionIds, $date);
        $availableTimes = $availability['times'];

        // 合計所要時間・合計料金
        $totalDuration = $menu->is_event ? 0 : $menu->duration;
        $totalPrice = $menu->price;
        foreach ($options as $option) {
            $totalDuration += $option->duration;
            $totalPrice += $option->price;
        }

        return view('reservations.times', [
            'menu' => $menu,
            'options' => $options,
            'optionIds' => $optionIds,
            'date' => Carbon::createFromFormat('Y-m-d', $date),
            'availableTimes' => $availableTimes,
            'availabilityReason' => $availability['reason'],
            'eventSlotDetails' => $availability['slot_details'] ?? [],
            'totalDuration' => $totalDuration,
            'totalPrice' => $totalPrice,
        ]);
    }

    /**
     * 予約確認画面
     */
    public function confirm(Request $request)
    {
        $request->validate([
            'menu_id' => 'required|exists:menus,id',
            'date' => 'required|date_format:Y-m-d|after_or_equal:today',
            'start_time' => 'nullable|date_format:H:i',
        ]);

        $menuId = $request->input('menu_id');
        $optionIds = $request->input('options', []);
        $date = $request->input('date');
        $startTime = $request->input('start_time');

        if (! $this->isDateReservableForUsers($date)) {
            return redirect()->route('reservations.calendar', [
                'menu_id' => $menuId,
                'options' => $optionIds,
                'month' => now('Asia/Tokyo')->format('Y-m'),
            ])->with('availability_reason', self::MONTH_UNPUBLISHED_REASON);
        }

        $menu = Menu::findOrFail($menuId);

        if (! $menu->is_event && ! $startTime) {
            throw ValidationException::withMessages([
                'start_time' => '開始時刻を選択してください。',
            ]);
        }

        $options = ! empty($optionIds)
            ? $this->resolveOptions($menu, $optionIds)
            : collect();

        $availabilityService = new AvailabilityService;
        $slotId = null;

        if ($menu->is_event) {
            if (! $startTime) {
                abort(422);
            }

            $slot = $availabilityService->findReservableEventSlot($menu, $date, $startTime);

            if (! $slot) {
                return redirect()->route('reservations.times', [
                    'menu_id' => $menuId,
                    'date' => $date,
                ] + ($optionIds !== [] ? ['options' => $optionIds] : []))->withErrors([
                    'start_time' => '選択したイベント枠は現在予約できません。',
                ]);
            }

            $slotId = $slot->id;
            $startDateTime = Carbon::createFromFormat(
                'Y-m-d H:i',
                $date.' '.$slot->start_time->format('H:i'),
                'Asia/Tokyo'
            );
            $endDateTime = Carbon::createFromFormat(
                'Y-m-d H:i',
                $date.' '.$slot->end_time->format('H:i'),
                'Asia/Tokyo'
            );
            $startTime = $slot->start_time->format('H:i');
            $totalDuration = $startDateTime->diffInMinutes($endDateTime);
            $totalPrice = $menu->price;
        } else {
            $totalDuration = $menu->duration;
            $totalPrice = $menu->price;
            foreach ($options as $option) {
                $totalDuration += $option->duration;
                $totalPrice += $option->price;
            }

            $startDateTime = Carbon::createFromFormat(
                'Y-m-d H:i',
                "$date $startTime",
                'Asia/Tokyo'
            );
            $endDateTime = $startDateTime->clone()->addMinutes($totalDuration);
        }

        return view('reservations.confirm', [
            'menu' => $menu,
            'options' => $options,
            'date' => $startDateTime->toDateString(),
            'startTime' => $startTime,
            'endTime' => $endDateTime->format('H:i'),
            'slotId' => $slotId,
            'totalDuration' => $totalDuration,
            'totalPrice' => $totalPrice,
        ]);
    }

    /**
     * 予約作成
     */
    public function store(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();

        $request->validate([
            'menu_id' => 'required|exists:menus,id',
            'date' => 'required|date_format:Y-m-d|after_or_equal:today',
            'start_time' => 'nullable|date_format:H:i',
            'slot_id' => 'nullable|exists:slots,id',
            'options' => 'nullable|array',
            'options.*' => 'exists:menu_options,id',
        ]);

        $menuId = $request->input('menu_id');
        $optionIds = $request->input('options', []);
        $date = $request->input('date');
        $startTime = $request->input('start_time');
        $requestedSlotId = $request->input('slot_id');

        if (! $this->isDateReservableForUsers($date)) {
            return redirect()->route('reservations.calendar', [
                'menu_id' => $menuId,
                'options' => $optionIds,
                'month' => now('Asia/Tokyo')->format('Y-m'),
            ])->with('availability_reason', self::MONTH_UNPUBLISHED_REASON);
        }

        $menu = Menu::findOrFail($menuId);
        $options = ! empty($optionIds)
            ? $this->resolveOptions($menu, $optionIds)
            : collect();

        try {
            $reservation = null;

            DB::transaction(function () use (
                &$reservation,
                $menu,
                $options,
                $optionIds,
                $date,
                $startTime,
                $requestedSlotId,
                $user,
            ) {
                $availabilityService = new AvailabilityService;
                if ($menu->is_event) {
                    $alreadyReserved = Reservation::query()
                        ->where('user_id', $user->id)
                        ->where('menu_id', $menu->id)
                        ->whereDate('date', $date)
                        ->where('status', 'confirmed')
                        ->lockForUpdate()
                        ->exists();

                    if ($alreadyReserved) {
                        throw ValidationException::withMessages([
                            'start_time' => '同じイベントは1日につき1回まで予約できます。',
                        ]);
                    }

                    $slot = Slot::query()
                        ->with('menu')
                        ->whereKey($requestedSlotId)
                        ->lockForUpdate()
                        ->first();

                    if (! $slot && $startTime) {
                        $slot = Slot::query()
                            ->with('menu')
                            ->where('menu_id', $menu->id)
                            ->whereDate('date', $date)
                            ->where('start_time', $startTime)
                            ->lockForUpdate()
                            ->first();
                    }

                    if (! $slot || $slot->menu_id !== $menu->id || $slot->date->toDateString() !== $date) {
                        throw ValidationException::withMessages([
                            'start_time' => '選択したイベント枠を確認できませんでした。',
                        ]);
                    }

                    $confirmedCount = Reservation::query()
                        ->where('slot_id', $slot->id)
                        ->where('status', 'confirmed')
                        ->lockForUpdate()
                        ->get(['id'])
                        ->count();

                    if ($slot->capacity === null || $confirmedCount >= $slot->capacity) {
                        throw ValidationException::withMessages([
                            'start_time' => 'このイベント枠は満席です。',
                        ]);
                    }

                    $reservation = Reservation::create([
                        'user_id' => $user->id,
                        'menu_id' => $menu->id,
                        'slot_id' => $slot->id,
                        'date' => $slot->date->toDateString(),
                        'start_time' => $slot->start_time->format('H:i'),
                        'end_time' => $slot->end_time->format('H:i'),
                        'status' => 'confirmed',
                    ]);
                } else {
                    $startDateTime = Carbon::createFromFormat(
                        'Y-m-d H:i',
                        "$date $startTime",
                        'Asia/Tokyo'
                    );

                    $totalDuration = $menu->duration;
                    foreach ($options as $option) {
                        $totalDuration += $option->duration;
                    }

                    $endDateTime = $startDateTime->clone()->addMinutes($totalDuration);

                    Reservation::where('date', $startDateTime->toDateString())
                        ->where('status', 'confirmed')
                        ->lockForUpdate()
                        ->get();

                    $availableTimes = $availabilityService->getAvailableTimes($menu, $optionIds, $startDateTime->toDateString());
                    if (! in_array($startDateTime->format('H:i'), $availableTimes, true)) {
                        throw ValidationException::withMessages([
                            'start_time' => 'この時間帯は既に予約されています。',
                        ]);
                    }

                    $reservation = Reservation::create([
                        'user_id' => $user->id,
                        'menu_id' => $menu->id,
                        'slot_id' => null,
                        'date' => $startDateTime->toDateString(),
                        'start_time' => $startDateTime->format('H:i'),
                        'end_time' => $endDateTime->format('H:i'),
                        'status' => 'confirmed',
                    ]);
                }

                if ($options->isNotEmpty()) {
                    $reservation->options()->attach($options->pluck('id'));
                }
            });

            if (! $reservation instanceof Reservation) {
                return redirect()->route('menus.index')->with('error', '予約の作成に失敗しました。');
            }

            $this->notificationService->sendReservationConfirmedToUser($reservation);
            $this->notificationService->sendAdminNotification($reservation, 'confirmed');

            return redirect()->route('reservations.complete', ['reservation' => $reservation->id]);
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        }
    }

    /**
     * 予約完了画面
     */
    public function complete(Reservation $reservation)
    {
        /** @var User $user */
        $user = Auth::user();

        // 自分の予約のみ表示可能
        if ($reservation->user_id !== $user->id && ! $user->is_admin) {
            abort(403);
        }

        $reservation->load(['menu', 'options']);

        return view('reservations.complete', compact('reservation'));
    }

    private function isDateReservableForUsers(string $date): bool
    {
        $targetDate = Carbon::createFromFormat('Y-m-d', $date, 'Asia/Tokyo')->startOfDay();

        return $this->isMonthVisibleToUsers($targetDate);
    }

    private function isMonthVisibleToUsers(Carbon $month): bool
    {
        $availabilityService = new AvailabilityService;

        return $availabilityService->isMonthPublicForUsers($month);
    }

    private function resolveFallbackPublishedMonth(): ?Carbon
    {
        $yearMonth = ReservationPublicationMonth::query()
            ->where('is_published', true)
            ->orderBy('year_month')
            ->value('year_month');

        if (! is_string($yearMonth)) {
            return null;
        }

        return Carbon::createFromFormat('Y-m', $yearMonth, 'Asia/Tokyo')->startOfMonth();
    }

    private function resolveOptions(Menu $menu, array $optionIds)
    {
        if ($menu->is_event || $optionIds === []) {
            return collect();
        }

        return MenuOption::whereIn('id', $optionIds)->active()->get();
    }
}
