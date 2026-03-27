<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;

class OfflineCreateWidget extends Widget
{
    protected string $view = 'filament.widgets.offline-create-widget';
    protected int | string | array $columnSpan = 'full';
}
