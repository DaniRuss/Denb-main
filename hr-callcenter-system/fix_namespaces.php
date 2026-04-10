<?php

$resourcesPath = __DIR__ . '/app/Filament/Resources/';

$updates = [
    [
        'oldDir' => 'UniformDistributions',
        'newDir' => 'UniformDistributionResource',
        'resourceFile' => 'UniformDistributionResource.php'
    ],
    [
        'oldDir' => 'UniformInventories',
        'newDir' => 'UniformInventoryResource',
        'resourceFile' => 'UniformInventoryResource.php'
    ]
];

foreach ($updates as $update) {
    $oldDirPath = $resourcesPath . $update['oldDir'];
    $newDirPath = $resourcesPath . $update['newDir'];
    $resourceFilePath = $resourcesPath . $update['resourceFile'];

    // Rename Directory
    if (is_dir($oldDirPath)) {
        rename($oldDirPath, $newDirPath);
    }

    // Update Pages Namespaces
    if (is_dir($newDirPath . '/Pages')) {
        $files = glob($newDirPath . '/Pages/*.php');
        foreach ($files as $file) {
            $content = file_get_contents($file);
            $content = str_replace($update['oldDir'] . '\\Pages', $update['newDir'] . '\\Pages', $content);
            file_put_contents($file, $content);
        }
    }

    // Update Resource File Imports
    if (file_exists($resourceFilePath)) {
        $content = file_get_contents($resourceFilePath);
        $content = str_replace($update['oldDir'] . '\\Pages', $update['newDir'] . '\\Pages', $content);
        file_put_contents($resourceFilePath, $content);
    }
}

echo "Namespaces fixed completely!\n";
