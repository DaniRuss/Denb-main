<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = \App\Models\User::whereHas('roles', function($q) {
  $q->where('name', 'like', '%officer%');
})->first();

if ($user) {
    echo "=============================================\n";
    echo "OFFICER FOUND!\n";
    echo "Email: " . $user->email . "\n";
    
    $roles = $user->getRoleNames()->toArray();
    echo "Role(s): " . implode(', ', $roles) . "\n";

    // Standard fallback password for local seeded data testing
    $user->password = \Illuminate\Support\Facades\Hash::make('password');
    $user->save();
    
    echo "Password: password\n";
    echo "=============================================\n";
} else {
    echo "NO OFFICER FOUND IN DATABASE.\n";
}
