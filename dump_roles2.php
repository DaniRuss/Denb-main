<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$roles = \Spatie\Permission\Models\Role::with('permissions')->get();
$data = [];
foreach ($roles as $role) {
    if ($role) {
        $perms = $role->permissions ? $role->permissions->pluck('name')->toArray() : [];
        $data[$role->name] = $perms;
    }
}
file_put_contents('roles.json', json_encode($data, JSON_PRETTY_PRINT));
echo "Done\n";
