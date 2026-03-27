<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\AwarenessEngagement;
use App\Models\User;

$engagements = AwarenessEngagement::all();
echo "Total Engagements: " . $engagements->count() . "\n";
foreach ($engagements as $e) {
    echo "ID: {$e->id} | Woreda: {$e->woreda_id} | Status: {$e->status} | Creator: {$e->created_by}\n";
}

$u = User::where('email', 'coordinator@aalea.gov.et')->first();
if ($u) {
    echo "Coordinator Email: {$u->email} | Woreda: {$u->woreda_id} | SubCity: {$u->sub_city_id}\n";
    echo "Roles: " . $u->roles->pluck('name')->implode(', ') . "\n";
} else {
    echo "Coordinator not found.\n";
}
