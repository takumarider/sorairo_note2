<?php

namespace App\Mail;

use App\Models\Reservation;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ReservationCanceled extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Reservation $reservation
    ) {}

    public function build()
    {
        return $this->subject('【Sorairo Note】予約キャンセルのお知らせ')
            ->markdown('emails.reservations.canceled');
    }
}
