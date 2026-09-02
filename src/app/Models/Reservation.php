<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Reservation extends Model
{
    /** @use HasFactory<\Database\Factories\ReservationFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'menu_id',
        'slot_id',
        'date',
        'start_time',
        'end_time',
        'status',
        'canceled_at',
        'total_price',
        'total_duration',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'start_time' => 'datetime:H:i',
            'end_time' => 'datetime:H:i',
            'canceled_at' => 'datetime',
            'total_price' => 'integer',
            'total_duration' => 'integer',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function menu()
    {
        return $this->belongsTo(Menu::class);
    }

    public function slot()
    {
        return $this->belongsTo(Slot::class);
    }

    public function options()
    {
        return $this->belongsToMany(MenuOption::class, 'reservation_options');
    }

    public function cancel(): void
    {
        $this->update([
            'status' => 'canceled',
            'canceled_at' => now(),
        ]);

        // 旧スロット方式の施術予約のみ予約済みフラグを戻す
        if ($this->slot_id !== null) {
            $slot = $this->slot()->with('menu')->first();

            if ($slot && ! ($slot->menu?->is_event ?? false)) {
                $slot->update(['is_reserved' => false]);
            }
        }
    }

    public function canCancel(): bool
    {
        /** @var User|null $actor */
        $actor = Auth::user();

        return $this->canCancelBy($actor);
    }

    public function canCancelBy(?User $actor): bool
    {
        if (! $actor) {
            return false;
        }

        if ($this->status !== 'confirmed') {
            return false;
        }

        if ($actor->is_admin) {
            return true;
        }

        if ($this->user_id !== $actor->id) {
            return false;
        }

        $startAt = $this->resolveStartDateTime();

        if (! $startAt) {
            return false;
        }

        $settings = SystemSetting::getSingleton();
        $deadlineHours = $settings->userCancelDeadlineHours();
        $deadlineAt = $startAt->copy()->subHours($deadlineHours);

        return now('Asia/Tokyo')->lessThanOrEqualTo($deadlineAt);
    }

    public function cancellationFailureReasonBy(?User $actor): string
    {
        if (! $actor) {
            return '認証情報を確認できませんでした。再度ログインしてください。';
        }

        if ($this->status !== 'confirmed') {
            return 'この予約はキャンセルできません。';
        }

        if (! $actor->is_admin && $this->user_id !== $actor->id) {
            return 'この予約をキャンセルする権限がありません。';
        }

        if ($actor->is_admin) {
            return 'この予約はキャンセルできません。';
        }

        $startAt = $this->resolveStartDateTime();

        if (! $startAt) {
            return '予約日時を確認できないため、キャンセルできません。';
        }

        $settings = SystemSetting::getSingleton();
        $contactNumber = trim((string) ($settings->welcome_contact_number ?? ''));

        if ($contactNumber !== '') {
            return sprintf('キャンセル期限を過ぎたため、サロンまで直接ご連絡ください。詳しくはWelcomeページのお問い合わせ（%s）をご確認ください。', $contactNumber);
        }

        return 'キャンセル期限を過ぎたため、サロンまで直接ご連絡ください。詳しくはWelcomeページのお問い合わせをご確認ください。';
    }

    /**
     * 予約時点の合計料金を返す。
     * 予約作成時に total_price が保存されていればその値を、
     * 未設定（機能追加前の既存データ）の場合はメニュー・オプションから算出した値を返す。
     */
    public function resolvedTotalPrice(): int
    {
        if ($this->total_price !== null) {
            return (int) $this->total_price;
        }

        return $this->fallbackTotalPrice();
    }

    /**
     * 予約時点の合計所要時間（分）を返す。
     * total_duration が未設定の場合は開始〜終了時刻の差分から算出する。
     */
    public function resolvedTotalDuration(): int
    {
        if ($this->total_duration !== null) {
            return (int) $this->total_duration;
        }

        return $this->fallbackTotalDuration();
    }

    private function fallbackTotalPrice(): int
    {
        $menu = $this->relationLoaded('menu') ? $this->menu : $this->menu()->first();

        if (! $menu) {
            return 0;
        }

        $price = (int) $menu->price;

        if (! $menu->is_event) {
            $options = $this->relationLoaded('options') ? $this->options : $this->options()->get();
            $price = max(0, $price + (int) $options->sum('price'));
        }

        return $price;
    }

    private function fallbackTotalDuration(): int
    {
        if (! $this->start_time || ! $this->end_time) {
            return 0;
        }

        return max(0, $this->start_time->diffInMinutes($this->end_time));
    }

    protected function resolveStartDateTime(): ?Carbon
    {
        $date = $this->date ?? $this->slot?->date;
        $time = $this->start_time ?? $this->slot?->start_time;

        if (! $date || ! $time) {
            return null;
        }

        return Carbon::createFromFormat(
            'Y-m-d H:i:s',
            $date->toDateString().' '.$time->format('H:i:s'),
            'Asia/Tokyo'
        );
    }
}
