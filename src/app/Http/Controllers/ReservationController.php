<?php

namespace App\Http\Controllers;

use App\Mail\ReservationConfirmed;
use App\Models\Menu;
use App\Models\MenuOption;
use App\Models\Reservation;
use App\Models\Slot;
use App\Models\SystemSetting;
use App\Models\User;
use App\Services\AvailabilityService;
use App\Services\NotificationService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

class ReservationController extends Controller
{
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
            ? Carbon::createFromFormat('Y-m', $validated['month'])
            : now();

        $menu = Menu::findOrFail($menuId);
        $options = ! empty($optionIds)
            ? MenuOption::whereIn('id', $optionIds)->active()->get()
            : collect();

        $availabilityService = new AvailabilityService();
        $availableDates = $availabilityService->getAvailableDates($menu, $optionIds, $month);
        $availabilitySummary = $availabilityService->getMonthlyAvailabilitySummary($menu, $optionIds, $month);

        // 合計所要時間・合計料金を計算
        $totalDuration = $menu->duration;
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

        $menu = Menu::findOrFail($menuId);
        $options = ! empty($optionIds)
            ? MenuOption::whereIn('id', $optionIds)->active()->get()
            : collect();

        $availabilityService = new AvailabilityService();
        $availability = $availabilityService->getAvailableTimesWithReason($menu, $optionIds, $date);
        $availableTimes = $availability['times'];

        // 合計所要時間・合計料金
        $totalDuration = $menu->duration;
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
            'start_time' => 'required|date_format:H:i',
        ]);

        $menuId = $request->input('menu_id');
        $optionIds = $request->input('options', []);
        $date = $request->input('date');
        $startTime = $request->input('start_time');

        $menu = Menu::findOrFail($menuId);
        $options = ! empty($optionIds)
            ? MenuOption::whereIn('id', $optionIds)->active()->get()
            : collect();

        // 開始時刻と所要時間から終了時刻を計算
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

        return view('reservations.confirm', [
            'menu' => $menu,
            'options' => $options,
            'date' => $startDateTime->toDateString(),
            'startTime' => $startTime,
            'endTime' => $endDateTime->format('H:i'),
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
            'start_time' => 'required|date_format:H:i',
            'options' => 'nullable|array',
            'options.*' => 'exists:menu_options,id',
        ]);

        $menuId = $request->input('menu_id');
        $optionIds = $request->input('options', []);
        $date = $request->input('date');
        $startTime = $request->input('start_time');

        $menu = Menu::findOrFail($menuId);
        $options = ! empty($optionIds)
            ? MenuOption::whereIn('id', $optionIds)->active()
                ->get()
            : collect();

        // 合計所要時間を計算
        $totalDuration = $menu->duration;
        foreach ($options as $option) {
            $totalDuration += $option->duration;
        }

        $startDateTime = Carbon::createFromFormat(
            'Y-m-d H:i',
            "$date $startTime",
            'Asia/Tokyo'
        );
        $endDateTime = $startDateTime->clone()->addMinutes($totalDuration);

        try {
            $reservation = null;

            DB::transaction(function () use (
                &$reservation,
                $menu,
                $options,
                $optionIds,
                $startDateTime,
                $endDateTime,
                $user,
            ) {
                // 二重予約防止：その日時の予約をロック
                $conflictReservations = Reservation::where('date', $startDateTime->toDateString())
                    ->where('status', 'confirmed')
                    ->lockForUpdate()
                    ->get();

                $availabilityService = new AvailabilityService();
                $dateCarbon = $startDateTime->clone()->startOfDay();
                $reservedRanges = $availabilityService->getReservedRanges($dateCarbon);

                // 再度確認：重複チェック
                $availableTimes = $availabilityService->getAvailableTimes($menu, $optionIds, $dateCarbon->toDateString());
                if (! in_array($startDateTime->format('H:i'), $availableTimes)) {
                    throw ValidationException::withMessages([
                        'start_time' => 'この時間帯は既に予約されています。',
                    ]);
                }

                // 予約を作成
                $reservation = Reservation::create([
                    'user_id' => $user->id,
                    'menu_id' => $menu->id,
                    'slot_id' => null, // 新方式ではslot_idは不要
                    'date' => $startDateTime->toDateString(),
                    'start_time' => $startDateTime->format('H:i'),
                    'end_time' => $endDateTime->format('H:i'),
                    'status' => 'confirmed',
                ]);

                // オプションを関連付け
                if ($options->isNotEmpty()) {
                    $reservation->options()->attach($options->pluck('id'));
                }

                // メール送信
                $this->notificationService->applyFromSettings(SystemSetting::first());
                Mail::to($user->email)
                    ->send(new ReservationConfirmed($reservation));

                // 管理者通知
                $this->notificationService->sendAdminNotification($reservation, 'confirmed');
            });

            if (! $reservation instanceof Reservation) {
                return redirect()->route('menus.index')->with('error', '予約の作成に失敗しました。');
            }

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
}

