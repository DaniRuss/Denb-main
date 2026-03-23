<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "--- DEPARTMENTS ---\n";
$departments = \App\Models\Department::all();
echo "Total Departments: " . $departments->count() . "\n";
foreach ($departments as $dept) {
    echo "- " . $dept->name_en . " (" . $dept->name_am . ")\n";
}

echo "\n--- EMPLOYEE TYPES ---\n";
// Sometimes officer is represented in employee types
$types = \App\Models\Employee::select('employee_type', \Illuminate\Support\Facades\DB::raw('count(*) as total'))->groupBy('employee_type')->get();
foreach ($types as $type) {
    echo "- " . $type->employee_type . ": " . $type->total . "\n";
}

echo "\n--- OFFICER RANKS ---\n";
$ranks = \App\Models\Officer::select('rank', \Illuminate\Support\Facades\DB::raw('count(*) as total'))->groupBy('rank')->get();
if ($ranks->isEmpty()) {
    echo "No officers currently initialized in the database.\n";
}
foreach ($ranks as $rank) {
    echo "- " . $rank->rank . ": " . $rank->total . "\n";
}

echo "\n--- OFFICER ROLES (Spatie) ---\n";
$roles = \Spatie\Permission\Models\Role::where('name', 'like', '%officer%')->get();
foreach ($roles as $role) {
    echo "- " . $role->name . "\n";
}
