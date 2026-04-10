<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

try {
    DB::connection()->getPdo();
    $email = 'supervisor@example.com';
    $password = 'password123';

    $user = User::updateOrCreate(
        ['email' => $email],
        [
            'name' => 'Test Supervisor',
            'username' => 'supervisor_user',
            'password' => Hash::make($password),
        ]
    );

    $user->assignRole('supervisor');
    echo "Success\n";
} catch (\Exception $e) {
    echo "PLAIN ERROR: " . $e->getMessage() . "\n";
}
