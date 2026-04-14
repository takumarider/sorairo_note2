<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TimeBlock;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TimeBlockApiController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        if (! $user || ! $user->is_admin) {
            return response()->json([
                'success' => false,
                'message' => '操作権限がありません。',
            ], 403);
        }

        $start = $request->input('start');
        $end = $request->input('end');

        $query = TimeBlock::query()
            ->where('is_active', true)
            ->orderBy('start_at');

        if ($start && $end) {
            $rangeStart = Carbon::parse($start, 'Asia/Tokyo');
            $rangeEnd = Carbon::parse($end, 'Asia/Tokyo');
            $query->where('start_at', '<', $rangeEnd)
                ->where('end_at', '>', $rangeStart);
        }

        return response()->json([
            'success' => true,
            'blocks' => $query->get()->map(fn (TimeBlock $block): array => [
                'id' => $block->id,
                'start_at' => $block->start_at->format('Y-m-d\\TH:i:s'),
                'end_at' => $block->end_at->format('Y-m-d\\TH:i:s'),
                'reason' => $block->reason,
                'is_active' => $block->is_active,
            ])->values(),
        ]);
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        if (! $user || ! $user->is_admin) {
            return response()->json([
                'success' => false,
                'message' => '操作権限がありません。',
            ], 403);
        }

        $validated = $request->validate([
            'start_at' => 'required|date',
            'end_at' => 'required|date|after:start_at',
            'reason' => 'nullable|string|max:255',
        ]);

        $startAt = Carbon::parse($validated['start_at'], 'Asia/Tokyo');
        $endAt = Carbon::parse($validated['end_at'], 'Asia/Tokyo');

        if ($startAt->diffInMinutes($endAt) % 30 !== 0) {
            return response()->json([
                'success' => false,
                'message' => 'ブロック時間は30分単位で指定してください。',
            ], 422);
        }

        $block = TimeBlock::create([
            'start_at' => $startAt,
            'end_at' => $endAt,
            'reason' => $validated['reason'] ?? null,
            'is_active' => true,
        ]);

        return response()->json([
            'success' => true,
            'message' => '時間帯ブロックを作成しました。',
            'block' => [
                'id' => $block->id,
                'start_at' => $block->start_at->format('Y-m-d\\TH:i:s'),
                'end_at' => $block->end_at->format('Y-m-d\\TH:i:s'),
                'reason' => $block->reason,
            ],
        ]);
    }

    public function destroy(TimeBlock $timeBlock)
    {
        $user = Auth::user();
        if (! $user || ! $user->is_admin) {
            return response()->json([
                'success' => false,
                'message' => '操作権限がありません。',
            ], 403);
        }

        $timeBlock->delete();

        return response()->json([
            'success' => true,
            'message' => '時間帯ブロックを削除しました。',
        ]);
    }
}
