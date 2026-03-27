<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;

$u = User::where('email', 'coordinator@aalea.gov.et')->first();
if ($u) {
    echo "Coordinator Email: " . $u->email . "\n";
    echo "Permissions: " . $u->getAllPermissions()->pluck('name')->implode(', ') . "\n";
    echo "Can view_engagements: " . ($u->can('view_engagements') ? 'Yes' : 'No') . "\n";
} else {
    echo "Coordinator not found.\n";
}
