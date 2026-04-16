<?php

namespace App\Notifications;

use App\Models\ConfiscatedAsset;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class TransferOverdueNotification extends Notification
{
    use Queueable;

    public function __construct(
        public ConfiscatedAsset $asset,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Asset Transfer Overdue',
            'title_am' => 'የንብረት ማስተላለፊያ ጊዜ አልፏል',
            'body' => "Asset \"{$this->asset->description}\" — transfer deadline was {$this->asset->transfer_deadline->format('Y-m-d')}.",
            'body_am' => "ንብረት \"{$this->asset->description}\" — የማስተላለፊያ ገደቡ {$this->asset->transfer_deadline->format('Y-m-d')} ነበር።",
            'type' => 'transfer_overdue',
            'confiscated_asset_id' => $this->asset->id,
            'violation_record_id' => $this->asset->violation_record_id,
        ];
    }
}
