<?php
$it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator('app/Filament/Resources'));
foreach ($it as $file) {
    if ($file->isFile()) {
        echo $file->getPathname() . PHP_EOL;
    }
}
