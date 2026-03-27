<?php
require __DIR__.'/vendor/autoload.php';
try {
    include __DIR__.'/app/Filament/Pages/Outbox.php';
} catch (\Throwable $e) {
    echo $e->getMessage();
}
