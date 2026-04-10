<?php

use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    $roles = Role::all();
    $output = "Here are the login credentials for each role:\n\n";

    foreach ($roles as $role) {
        // Try to find an existing user with this role
        $user = User::role($role->name)->first();

        // If no user exists, create one safely
        if (!$user) {
            $username = str_replace(' ', '_', strtolower($role->name));
             // Check if username is taken, append random str
            if (User::where('username', $username)->exists()) {
                $username .= '_' . Str::random(4);
            }
            
            $email = $username . '@aalea.gov.et';

            $user = User::create([
                'name' => ucwords(str_replace('_', ' ', $role->name)) . ' User',
                'username' => $username,
                'email' => $email,
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
                'remember_token' => Str::random(10),
            ]);
            $user->assignRole($role->name);
        } else {
            // Reset existing user's password so we know what it is
            if ($role->name !== 'admin') {
                $user->password = Hash::make('password123');
                $user->save();
            }
        }

        $password = ($role->name === 'admin') ? 'admin123' : 'password123';

        $output .= "- **Role**: " . $role->name . "\n";
        $output .= "  - **Username/Email**: " . $user->email . " (or " . $user->username . ")\n";
        $output .= "  - **Password**: " . $password . "\n\n";
    }

    file_put_contents('test_users.txt', $output);
    echo "Users processed successfully.";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage();
}
