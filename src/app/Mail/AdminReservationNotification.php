<?php

namespace App\Mail;

use App\Models\Reservation;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AdminReservationNotification extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Reservation $reservation,
        public string $type
    ) {}

    public function build()
    {
        $subject = $this->type === 'confirmed'
            ? '【Sorairo Note】新規予約通知'
            : '【Sorairo Note】予約キャンセル通知';

        return $this->subject($subject)
            ->markdown('emails.admin.reservation-notification');
    }
}
