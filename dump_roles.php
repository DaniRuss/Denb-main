<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$roles = \Spatie\Permission\Models\Role::with('permissions')->get();
$data = [];
foreach ($roles as $role) {
    $data[$role->name] = $role->permissions->pluck('name')->toArray();
}
echo json_encode($data, JSON_PRETTY_PRINT);
