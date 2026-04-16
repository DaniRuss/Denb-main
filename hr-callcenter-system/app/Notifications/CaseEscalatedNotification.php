<?php

namespace App\Notifications;

use App\Models\Escalation;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class CaseEscalatedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Escalation $escalation,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $ticket = $this->escalation->complaint?->ticket_number ?? 'N/A';
        $levels = [1 => 'Team Lead', 2 => 'Supervisor', 3 => 'Director', 4 => 'Commissioner'];
        $levelName = $levels[$this->escalation->level] ?? "Level {$this->escalation->level}";

        return [
            'title' => "Case Escalated to {$levelName}",
            'title_am' => "ጉዳይ ወደ {$levelName} ተላልፏል",
            'body' => "Complaint {$ticket} escalated. Reason: {$this->escalation->reason}",
            'body_am' => "ቅሬታ {$ticket} ተላልፏል። ምክንያት: {$this->escalation->reason}",
            'type' => 'case_escalated',
            'escalation_id' => $this->escalation->id,
            'complaint_id' => $this->escalation->complaint_id,
            'level' => $this->escalation->level,
        ];
    }
}
