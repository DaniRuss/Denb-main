<?php

namespace App\Widgets;

use App\Models\Tip;
use Filament\Widgets\Widget;

class CaseManagementWidget extends Widget
{
    protected string $view = 'filament.widgets.case-management';

    public function getPendingCount()
    {
        return \App\Models\Complaint::where('status', 'pending')->count();
    }

    public function getUrgentCount()
    {
        return \App\Models\Complaint::where('priority', 'high')
            ->whereIn('status', ['pending', 'assigned'])
            ->count();
    }

    public function getSupervisorQueueCount()
    {
        return Tip::where('tip_source', Tip::SOURCE_CALL_CENTER)
            ->where('status', Tip::STATUS_PENDING_SUPERVISOR_REVIEW)
            ->count();
    }

    public function getDirectorQueueCount()
    {
        return Tip::where('tip_source', Tip::SOURCE_CALL_CENTER)
            ->where('status', Tip::STATUS_PENDING_DIRECTOR_REVIEW)
            ->count();
    }
}
