<?php

namespace App\Notifications;

use App\Models\WarningLetter;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class WarningDeadlineNotification extends Notification
{
    use Queueable;

    public function __construct(
        public WarningLetter $letter,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Warning Deadline Expired',
            'title_am' => 'የማስጠንቀቂያ ገደብ አልፏል',
            'body' => "Warning #{$this->letter->reference_number} — deadline was {$this->letter->deadline->format('Y-m-d H:i')}.",
            'body_am' => "ማስጠንቀቂያ #{$this->letter->reference_number} — ገደቡ {$this->letter->deadline->format('Y-m-d H:i')} ነበር።",
            'type' => 'warning_expired',
            'warning_letter_id' => $this->letter->id,
            'violation_record_id' => $this->letter->violation_record_id,
        ];
    }
}
