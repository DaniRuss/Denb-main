<?php

namespace App\Notifications;

use App\Models\PenaltyReceipt;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class PaymentOverdueNotification extends Notification
{
    use Queueable;

    public function __construct(
        public PenaltyReceipt $receipt,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $record = $this->receipt->violationRecord;

        return [
            'title' => 'Payment Overdue',
            'title_am' => 'ክፍያ ጊዜ አልፎበታል',
            'body' => "Receipt #{$this->receipt->receipt_number} — deadline was {$this->receipt->payment_deadline->format('Y-m-d')}.",
            'body_am' => "ደረሰኝ #{$this->receipt->receipt_number} — ገደቡ {$this->receipt->payment_deadline->format('Y-m-d')} ነበር።",
            'type' => 'payment_overdue',
            'receipt_id' => $this->receipt->id,
            'violation_record_id' => $record?->id,
        ];
    }
}
