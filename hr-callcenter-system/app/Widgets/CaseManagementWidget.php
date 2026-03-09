<?php

namespace App\Widgets;

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

    public function getUnassignedTipsCount()
    {
        return \App\Models\Tip::whereNull('assigned_to')
            ->where('status', 'pending')
            ->count();
    }

    public function getImmediateTipsCount()
    {
        return \App\Models\Tip::where('urgency_level', 'immediate')
            ->where('status', 'pending')
            ->count();
    }
}
