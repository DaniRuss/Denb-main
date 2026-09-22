<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Employee;

$emp = Employee::where('user_id', 18)->with(['subCity', 'woreda'])->first();
if ($emp) {
    echo "Employee ID: {$emp->id}\n";
    echo "Sub-City: " . ($emp->subCity->name_en ?? 'N/A') . "\n";
    echo "Woreda: " . ($emp->woreda->name_en ?? 'N/A') . "\n";
} else {
    echo "No Employee record for User ID 18\n";
}
