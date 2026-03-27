<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\SubCity;
use App\Models\Woreda;

$bole = SubCity::where('name_am', 'like', '%ቦሌ%')
    ->orWhere('name_en', 'like', '%Bole%')
    ->first();

if ($bole) {
    echo "Bole SubCity ID: " . $bole->id . "\n";
    echo "Bole SubCity Name Am: " . $bole->name_am . "\n";
    
    $woreda1 = Woreda::where('sub_city_id', $bole->id)
        ->where(function($query) {
            $query->where('name_am', 'like', '%1%')
                  ->orWhere('name_en', 'like', '%1%');
        })
        ->first();
        
    if ($woreda1) {
        echo "Woreda 1 ID: " . $woreda1->id . "\n";
        echo "Woreda 1 Name Am: " . $woreda1->name_am . "\n";
    } else {
        echo "Woreda 1 Not Found in Bole\n";
    }
} else {
    echo "Bole SubCity Not Found\n";
}
