<?php

namespace App\Models;

use App\Mail\ReservationCanceled;
use App\Services\NotificationService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Mail;

class Reservation extends Model
{
    /** @use HasFactory<\Database\Factories\ReservationFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'menu_id',
        'slot_id',
        'status',
        'canceled_at',
    ];

    protected function casts(): array
    {
        return [
            'canceled_at' => 'datetime',
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

    public function cancel(): void
    {
        $this->update([
            'status' => 'canceled',
            'canceled_at' => now(),
        ]);

        $this->slot()->update(['is_reserved' => false]);

        app(NotificationService::class)->applyFromSettings(SystemSetting::first());

        Mail::to($this->user->email)
            ->send(new ReservationCanceled($this));

        app(NotificationService::class)->sendAdminNotification($this, 'canceled');
    }

    public function canCancel(): bool
    {
        if (! auth()->check()) {
            return false;
        }

        if ($this->user_id !== auth()->id() && ! auth()->user()->is_admin) {
            return false;
        }

        return $this->status === 'confirmed';
    }
}
