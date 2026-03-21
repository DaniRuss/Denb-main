<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use Spatie\Permission\Models\Role;

$user = User::find(2);

if ($user) {
    echo "User [ID: 2]: " . $user->name . " (" . $user->email . ")\n";
    echo "Roles: [" . $user->roles->pluck('name')->implode(', ') . "]\n";
    echo "--- Permissions for role 'admin' ---\n";
    $adminRole = \Spatie\Permission\Models\Role::findByName('admin');
    if ($adminRole) {
        echo "Admin Permissions: " . $adminRole->permissions->pluck('name')->implode(', ') . "\n";
    }
} else {
    echo "User 2 not found.\n";
}
