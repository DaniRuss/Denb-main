<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\DB;

echo "--- USERS AND ROLES ---\n";
$users = User::all();
foreach ($users as $user) {
    $roles = $user->roles->pluck('name')->implode(', ');
    echo "ID: {$user->id} | Name: {$user->name} | Email: {$user->email} | Roles: [{$roles}]\n";
}

echo "\n--- ROLES TABLE ---\n";
$roles = DB::table('roles')->get();
foreach ($roles as $role) {
    echo "ID: {$role->id} | Name: {$role->name}\n";
}
