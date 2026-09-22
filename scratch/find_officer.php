<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Employee;

$name = 'Kedir Ketema';
$user = User::where('name', 'like', "%$name%")->first();

if ($user) {
    echo "User Found: " . $user->name . " (Email: " . $user->email . ")\n";
    echo "Sub-City (from User): " . ($user->sub_city ?? 'N/A') . "\n";
    echo "Woreda (from User): " . ($user->woreda ?? 'N/A') . "\n";
    
    $employee = Employee::where('user_id', $user->id)->with(['subCity', 'woreda'])->first();
    if ($employee) {
        echo "Detailed Assignment:\n";
        echo "Sub-City: " . ($employee->subCity->name_en ?? 'N/A') . "\n";
        echo "Woreda: " . ($employee->woreda->name_en ?? 'N/A') . "\n";
    }
} else {
    echo "User '$name' not found.\n";
}
