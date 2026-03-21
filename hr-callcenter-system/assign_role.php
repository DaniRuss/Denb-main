<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\DB;

$adminRole = DB::table('roles')->where('name', 'admin')->first();

if ($adminRole) {
    $adminUsers = User::where('email', 'like', '%admin%')
        ->orWhere('name', 'like', '%Admin%')
        ->get();

    foreach ($adminUsers as $user) {
        $exists = DB::table('model_has_roles')
            ->where('role_id', $adminRole->id)
            ->where('model_id', $user->id)
            ->where('model_type', User::class)
            ->exists();

        if (!$exists) {
            DB::table('model_has_roles')->insert([
                'role_id' => $adminRole->id,
                'model_type' => User::class,
                'model_id' => $user->id
            ]);
            echo "Assigned 'admin' role to {$user->email}\n";
        } else {
            echo "User {$user->email} already has 'admin' role\n";
        }
    }
} else {
    echo "Admin role not found.\n";
}
