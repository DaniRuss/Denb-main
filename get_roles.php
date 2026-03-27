<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$roles = Spatie\Permission\Models\Role::all()->pluck('name')->toArray();
echo implode(', ', $roles);
