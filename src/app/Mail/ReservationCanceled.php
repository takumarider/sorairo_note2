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
        public Reservation $reservation,
        public ?string $customSubject = null,
        public ?string $customBody = null
    ) {}

    public function build()
    {
        $mail = $this->subject($this->customSubject ?: '【Sorairo Note】予約キャンセルのお知らせ');

        if (filled($this->customBody)) {
            return $mail->markdown('emails.custom-template', [
                'body' => $this->customBody,
            ]);
        }

        return $mail->markdown('emails.reservations.canceled');
    }
}
