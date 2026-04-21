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
        public string $type,
        public ?string $customSubject = null,
        public ?string $customBody = null
    ) {}

    public function build()
    {
        $defaultSubject = $this->type === 'confirmed'
            ? '【Sorairo Note】新規予約通知'
            : '【Sorairo Note】予約キャンセル通知';

        $mail = $this->subject($this->customSubject ?: $defaultSubject);

        if (filled($this->customBody)) {
            return $mail->markdown('emails.custom-template', [
                'body' => $this->customBody,
            ]);
        }

        return $mail->markdown('emails.admin.reservation-notification');
    }
}
