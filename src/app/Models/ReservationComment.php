<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReservationComment extends Model
{
    use HasFactory;

    public const SENDER_USER = 'user';

    public const SENDER_ADMIN = 'admin';

    protected $fillable = [
        'reservation_id',
        'sender_type',
        'sender_id',
        'body',
        'read_at',
        'hidden_by_user_at',
        'deleted_by_admin_at',
    ];

    protected function casts(): array
    {
        return [
            'read_at' => 'datetime',
            'hidden_by_user_at' => 'datetime',
            'deleted_by_admin_at' => 'datetime',
        ];
    }

    public function reservation(): BelongsTo
    {
        return $this->belongsTo(Reservation::class);
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }
}
