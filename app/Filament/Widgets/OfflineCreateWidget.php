<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;

/**
 * OfflineCreateWidget — shown on Create pages for both Engagement Logs and Volunteer Tips.
 *
 * Record type is detected client-side from the URL path (see the blade view),
 * so a single widget class serves both routes without any PHP property passing.
 */
class OfflineCreateWidget extends Widget
{
    protected string $view = 'filament.widgets.offline-create-widget';
    protected int | string | array $columnSpan = 'full';

    // Sort to top of header widgets
    protected static ?int $sort = -10;
}
