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
use App\Services\ReservationCommentService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ReservationController extends Controller
{
    private const MONTH_UNPUBLISHED_REASON = 'month_unpublished';

    public function __construct(
        private NotificationService $notificationService,
        private ReservationCommentService $commentService
    ) {}

    /**
     * 予約導線の入口。
     * 未ログイン時は認証へ誘導し、ログイン・登録後にカレンダーへ戻す。
     */
    public function start(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'menu_id' => 'required|exists:menus,id',
            'options' => 'nullable|array',
            'options.*' => 'exists:menu_options,id',
        ]);

        $calendarParams = [
            'menu_id' => $validated['menu_id'],
        ];

        if (! empty($validated['options'])) {
            $calendarParams['options'] = $validated['options'];
        }

        if (! Auth::check()) {
            $loginRedirect = redirect()->guest(route('login'));
            $request->session()->put('url.intended', route('reservations.calendar', $calendarParams, false));

            return $loginRedirect->with('status', '予約するためには「新規登録」が必要です。');
        }

        return redirect()->route('reservations.calendar', $calendarParams);
    }

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
            ? $this->parseYearMonth($validated['month'])
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

        $options = $this->resolveOptions($menu, $optionIds);

        $availabilityService = new AvailabilityService;
        $availableDates = $availabilityService->getAvailableDates($menu, $optionIds, $month);
        $availabilitySummary = $availabilityService->getMonthlyAvailabilitySummary($menu, $optionIds, $month);

        // 合計所要時間・合計料金を計算
        $totals = $availabilityService->calculateTotals($menu, $options);

        return view('reservations.calendar', [
            'menu' => $menu,
            'options' => $options,
            'optionIds' => $optionIds,
            'availableDates' => $availableDates,
            'availabilitySummary' => $availabilitySummary,
            'month' => $month,
            'totalDuration' => $totals['duration'],
            'totalPrice' => $totals['price'],
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

        $options = $this->resolveOptions($menu, $optionIds);

        $availabilityService = new AvailabilityService;
        $availability = $availabilityService->getAvailableTimesWithReason($menu, $optionIds, $date);
        $availableTimes = $availability['times'];

        // 合計所要時間・合計料金
        $totals = $availabilityService->calculateTotals($menu, $options);

        return view('reservations.times', [
            'menu' => $menu,
            'options' => $options,
            'optionIds' => $optionIds,
            'date' => Carbon::createFromFormat('Y-m-d', $date),
            'availableTimes' => $availableTimes,
            'availabilityReason' => $availability['reason'],
            'eventSlotDetails' => $availability['slot_details'] ?? [],
            'totalDuration' => $totals['duration'],
            'totalPrice' => $totals['price'],
        ]);
    }

    /**
     * 当日専用: 時間選択画面
     */
    public function sameDayTimes()
    {
        $today = now('Asia/Tokyo')->startOfDay();
        $date = $today->toDateString();

        if (! $this->isDateReservableForUsers($date)) {
            return view('reservations.same-day-times', [
                'date' => $today,
                'availableTimes' => [],
                'availabilityReason' => self::MONTH_UNPUBLISHED_REASON,
            ]);
        }

        $availableTimes = $this->getSameDayAvailableTimes($date);

        return view('reservations.same-day-times', [
            'date' => $today,
            'availableTimes' => $availableTimes,
            'availabilityReason' => empty($availableTimes) ? 'fully_booked' : 'available',
        ]);
    }

    /**
     * 当日専用: メニュー選択画面
     */
    public function sameDayMenus(Request $request)
    {
        $validated = $request->validate([
            'start_time' => 'required|date_format:H:i',
        ]);

        $today = now('Asia/Tokyo')->startOfDay();
        $date = $today->toDateString();
        $startTime = $validated['start_time'];

        if (! $this->isDateReservableForUsers($date)) {
            return redirect()->route('reservations.same-day.times')
                ->with('availability_reason', self::MONTH_UNPUBLISHED_REASON);
        }

        $availabilityService = new AvailabilityService;
        $startDateTime = Carbon::createFromFormat('Y-m-d H:i', $date.' '.$startTime, 'Asia/Tokyo');
        if ($availabilityService->isSameDayTreatmentTimeClosed($startDateTime)) {
            return redirect()->route('reservations.same-day.times')
                ->withErrors(['start_time' => '当日のこの時間は選択できません。']);
        }

        $menus = Menu::query()
            ->treatments()
            ->where('is_active', true)
            ->with(['options' => fn ($query) => $query->active()])
            ->orderedForDisplay()
            ->get()
            ->filter(function (Menu $menu) use ($availabilityService, $date, $startTime): bool {
                $availableTimes = $availabilityService->getAvailableTimes($menu, [], $date);

                return in_array($startTime, $availableTimes, true);
            })
            ->values();

        return view('reservations.same-day-menus', [
            'date' => $today,
            'startTime' => $startTime,
            'menus' => $menus,
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

        $options = $this->resolveOptions($menu, $optionIds);

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
            $totals = $availabilityService->calculateTotals($menu, $options, $startDateTime->diffInMinutes($endDateTime));
        } else {
            $startDateTime = Carbon::createFromFormat(
                'Y-m-d H:i',
                "$date $startTime",
                'Asia/Tokyo'
            );
            $totals = $availabilityService->calculateTotals($menu, $options);
            $endDateTime = $startDateTime->clone()->addMinutes($totals['duration']);
        }

        return view('reservations.confirm', [
            'menu' => $menu,
            'options' => $options,
            'date' => $startDateTime->toDateString(),
            'startTime' => $startTime,
            'endTime' => $endDateTime->format('H:i'),
            'slotId' => $slotId,
            'totalDuration' => $totals['duration'],
            'totalPrice' => $totals['price'],
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
            'comment' => 'nullable|string|max:1000',
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
        $options = $this->resolveOptions($menu, $optionIds);

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

                    $slotStartDateTime = Carbon::createFromFormat(
                        'Y-m-d H:i',
                        $slot->date->toDateString().' '.$slot->start_time->format('H:i'),
                        'Asia/Tokyo'
                    );

                    if ($slotStartDateTime->lt(now('Asia/Tokyo'))) {
                        throw ValidationException::withMessages([
                            'start_time' => '当日のこの時間は選択できません。',
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

                    $slotEndDateTime = Carbon::createFromFormat(
                        'Y-m-d H:i',
                        $slot->date->toDateString().' '.$slot->end_time->format('H:i'),
                        'Asia/Tokyo'
                    );
                    $totals = $availabilityService->calculateTotals($menu, $options, $slotStartDateTime->diffInMinutes($slotEndDateTime));

                    $reservation = Reservation::create([
                        'user_id' => $user->id,
                        'menu_id' => $menu->id,
                        'slot_id' => $slot->id,
                        'date' => $slot->date->toDateString(),
                        'start_time' => $slot->start_time->format('H:i'),
                        'end_time' => $slot->end_time->format('H:i'),
                        'status' => 'confirmed',
                        'total_price' => $totals['price'],
                        'total_duration' => $totals['duration'],
                    ]);
                } else {
                    $startDateTime = Carbon::createFromFormat(
                        'Y-m-d H:i',
                        "$date $startTime",
                        'Asia/Tokyo'
                    );

                    if ($availabilityService->isSameDayTreatmentTimeClosed($startDateTime)) {
                        throw ValidationException::withMessages([
                            'start_time' => '当日のこの時間は選択できません。',
                        ]);
                    }

                    $totals = $availabilityService->calculateTotals($menu, $options);
                    $endDateTime = $startDateTime->clone()->addMinutes($totals['duration']);

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
                        'total_price' => $totals['price'],
                        'total_duration' => $totals['duration'],
                    ]);
                }

                if ($options->isNotEmpty()) {
                    $reservation->options()->attach($options->pluck('id'));
                }
            });

            if (! $reservation instanceof Reservation) {
                return redirect()->route('menus.index')->with('error', '予約の作成に失敗しました。');
            }

            if ($request->filled('comment')) {
                $this->commentService->saveComment($reservation->id, $request->input('comment'));
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
        $comment = $this->commentService->getComment($reservation->id);

        return view('reservations.complete', compact('reservation', 'comment'));
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

        return $this->parseYearMonth($yearMonth);
    }

    private function parseYearMonth(string $yearMonth): Carbon
    {
        return Carbon::createFromFormat('!Y-m', $yearMonth, 'Asia/Tokyo')->startOfMonth();
    }

    /**
     * メニューに紐づく有効なオプションを解決する。
     * イベントメニューはオプションの対象外のため、常に空のコレクションを返す。
     */
    private function resolveOptions(Menu $menu, array $optionIds)
    {
        if ($menu->is_event || $optionIds === []) {
            return collect();
        }

        return MenuOption::whereIn('id', $optionIds)->where('menu_id', $menu->id)->active()->get();
    }

    private function getSameDayAvailableTimes(string $date): array
    {
        $availabilityService = new AvailabilityService;
        $menus = Menu::query()
            ->treatments()
            ->where('is_active', true)
            ->orderedForDisplay()
            ->get();

        $timeMap = [];

        foreach ($menus as $menu) {
            $times = $availabilityService->getAvailableTimes($menu, [], $date);
            foreach ($times as $time) {
                $timeMap[$time] = true;
            }
        }

        $availableTimes = array_keys($timeMap);
        sort($availableTimes);

        return $availableTimes;
    }
}
