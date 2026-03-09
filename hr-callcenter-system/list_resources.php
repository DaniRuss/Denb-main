<?php

use Illuminate\Support\Facades\Artisan;
use Filament\Facades\Filament;

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';

$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$resources = Filament::getResources();
file_put_contents('resources.json', json_encode($resources, JSON_PRETTY_PRINT));
echo "Saved to resources.json" . PHP_EOL;
