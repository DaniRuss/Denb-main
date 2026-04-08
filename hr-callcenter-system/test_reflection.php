<?php
require 'vendor/autoload.php';

use Filament\Resources\Pages\ListRecords;
use App\Filament\Resources\ComplaintResource\Pages\ListComplaints;

$reflection = new ReflectionProperty(ListRecords::class, 'resource');
echo "Parent Type: " . $reflection->getType() . "\n";
echo "Parent Static: " . ($reflection->isStatic() ? 'Yes' : 'No') . "\n";

try {
    $childReflection = new ReflectionProperty(ListComplaints::class, 'resource');
    echo "Child Type: " . $childReflection->getType() . "\n";
} catch (Exception $e) {
    echo "Error reflecting child: " . $e->getMessage() . "\n";
}
