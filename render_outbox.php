<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    echo view('filament.pages.outbox')->render();
} catch (\Throwable $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "FILE: " . $e->getFile() . " on line " . $e->getLine() . "\n";
    if ($e->getPrevious()) {
        echo "PREVIOUS: " . $e->getPrevious()->getMessage() . "\n";
    }
}
