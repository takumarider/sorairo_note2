<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use App\Models\MenuOption;
use App\Models\Reservation;
use App\Services\AvailabilityService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\Builder;

class ReservationApiController extends Controller
{
    public function events(Request $request)
    {
        $user = Auth::user();

        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => '認証情報を確認できません。',
            ], 401);
        }

        $query = Reservation::query()
            ->with('menu')
            ->where('status', 'confirmed')
            ->where(function (Builder $subQuery): void {
                $today = now('Asia/Tokyo')->toDateString();

                $subQuery
                    ->whereDate('date', '>=', $today)
                    ->orWhereHas('slot', fn (Builder $slotQuery) => $slotQuery->whereDate('date', '>=', $today));
            })
            ->orderBy('date')
            ->orderBy('start_time');

        if (! $user->is_admin) {
            $query->where('user_id', $user->id);
        }

        $reservations = $query->get();

        return response()->json([
            'success' => true,
            'reservations' => $reservations->map(function (Reservation $reservation): array {
                return [
                    'id' => $reservation->id,
                    'number' => str_pad((string) $reservation->id, 6, '0', STR_PAD_LEFT),
                    'date' => $reservation->date?->format('Y-m-d'),
                    'date_label' => $reservation->date?->locale('ja')->isoFormat('Y年M月D日(ddd)') ?? '日付未設定',
                    'time_label' => sprintf('%s - %s', $reservation->start_time?->format('H:i') ?? '--:--', $reservation->end_time?->format('H:i') ?? '--:--'),
                    'menu_name' => $reservation->menu?->name ?? 'メニュー未設定',
                    'price_label' => '¥'.number_format((int) ($reservation->menu?->price ?? 0)),
                    'cancel_url' => route('reservations.cancel', $reservation),
                    'api_cancel_url' => url('/api/reservations/'.$reservation->id),
                ];
            })->values(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'menu_id' => 'required|exists:menus,id',
            'date' => 'required|date_format:Y-m-d|after_or_equal:today',
            'start_time' => 'required|date_format:H:i',
            'options' => 'nullable|array',
            'options.*' => 'exists:menu_options,id',
        ]);

        $menu = Menu::findOrFail($validated['menu_id']);
        $optionIds = $validated['options'] ?? [];
        $options = ! empty($optionIds)
            ? MenuOption::whereIn('id', $optionIds)->active()->get()
            : collect();

        $totalDuration = $menu->duration + $options->sum('duration');

        $startDateTime = Carbon::createFromFormat(
            'Y-m-d H:i',
            $validated['date'].' '.$validated['start_time'],
            'Asia/Tokyo'
        );
        $endDateTime = $startDateTime->clone()->addMinutes($totalDuration);

        try {
            $reservation = null;

            DB::transaction(function () use (&$reservation, $menu, $options, $optionIds, $startDateTime, $endDateTime) {
                Reservation::where('date', $startDateTime->toDateString())
                    ->where('status', 'confirmed')
                    ->lockForUpdate()
                    ->get();

                $availabilityService = new AvailabilityService;
                $availableTimes = $availabilityService->getAvailableTimes(
                    $menu,
                    $optionIds,
                    $startDateTime->toDateString()
                );

                if (! in_array($startDateTime->format('H:i'), $availableTimes, true)) {
                    throw ValidationException::withMessages([
                        'start_time' => 'この時間帯は既に予約されています。',
                    ]);
                }

                $reservation = Reservation::create([
                    'user_id' => Auth::id(),
                    'menu_id' => $menu->id,
                    'slot_id' => null,
                    'date' => $startDateTime->toDateString(),
                    'start_time' => $startDateTime->format('H:i'),
                    'end_time' => $endDateTime->format('H:i'),
                    'status' => 'confirmed',
                ]);

                if ($options->isNotEmpty()) {
                    $reservation->options()->attach($options->pluck('id'));
                }
            });

            if (! $reservation instanceof Reservation) {
                return response()->json([
                    'success' => false,
                    'message' => '予約の作成に失敗しました。',
                ], 500);
            }

            $reservation->loadMissing(['menu', 'options']);

            return response()->json([
                'success' => true,
                'message' => '予約が完了しました。',
                'reservation' => [
                    'id' => $reservation->id,
                    'menu_name' => $reservation->menu->name,
                    'date' => $reservation->date->format('Y-m-d'),
                    'start_time' => $reservation->start_time->format('H:i'),
                    'end_time' => $reservation->end_time->format('H:i'),
                ],
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'この時間帯は既に予約されています。',
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '予約の作成に失敗しました。',
            ], 500);
        }
    }

    public function destroy(Reservation $reservation)
    {
        $user = Auth::user();

        if (! $user || ($reservation->user_id !== $user->id && ! $user->is_admin)) {
            return response()->json([
                'success' => false,
                'message' => 'この予約をキャンセルする権限がありません。',
            ], 403);
        }

        if (! $reservation->canCancel()) {
            $reason = $reservation->cancellationFailureReasonBy($user);

            return response()->json([
                'success' => false,
                'message' => $reason,
            ], 422);
        }

        try {
            $reservation->cancel();

            return response()->json([
                'success' => true,
                'message' => '予約をキャンセルしました。',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'キャンセルに失敗しました。',
            ], 500);
        }
    }
}
