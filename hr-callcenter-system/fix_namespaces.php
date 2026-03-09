<?php

// Fix all Filament resource files for Filament v5 compatibility

$resources = [
    [
        'file' => 'app/Filament/Resources/ComplaintResource.php',
        'old_pages_ns' => 'App\\Filament\\Resources\\ComplaintResource\\Pages',
        'new_pages_ns' => 'App\\Filament\\Resources\\Complaints\\Pages',
    ],
    [
        'file' => 'app/Filament/Resources/TipResource.php',
        'old_pages_ns' => 'App\\Filament\\Resources\\TipResource\\Pages',
        'new_pages_ns' => 'App\\Filament\\Resources\\Tips\\Pages',
    ],
    [
        'file' => 'app/Filament/Resources/EmployeeResource.php',
        'old_pages_ns' => 'App\\Filament\\Resources\\EmployeeResource\\Pages',
        'new_pages_ns' => 'App\\Filament\\Resources\\Employees\\Pages',
    ],
    [
        'file' => 'app/Filament/Resources/DepartmentResource.php',
        'old_pages_ns' => 'App\\Filament\\Resources\\DepartmentResource\\Pages',
        'new_pages_ns' => 'App\\Filament\\Resources\\Departments\\Pages',
    ],
];

foreach ($resources as $res) {
    if (!file_exists($res['file'])) {
        echo "File not found: {$res['file']}\n";
        continue;
    }

    $content = file_get_contents($res['file']);

    // 1. Fix Pages namespace import
    $content = str_replace(
        'use ' . $res['old_pages_ns'] . ';',
        'use ' . $res['new_pages_ns'] . ';',
        $content
    );

    // 2. Fix Forms\Components\* calls -> add proper imports or replace with full class
    // Since Forms\Components is not imported as a namespace alias, use FQCN
    $formComponents = [
        'TextInput',
        'TextArea',
        'Textarea',
        'Select',
        'Toggle',
        'Checkbox',
        'FileUpload',
        'Hidden',
        'Radio',
        'Placeholder',
        'Repeater',
        'DateTimePicker',
        'DatePicker',
        'TimePicker',
        'TagsInput',
        'ColorPicker',
        'MarkdownEditor',
        'RichEditor',
        'KeyValue',
        'CheckboxList',
        'Builder',
        'Section',
    ];

    // Replace Forms\Components\X with \Filament\Forms\Components\X
    foreach ($formComponents as $component) {
        $content = preg_replace(
            '/\bForms\\\\Components\\\\' . preg_quote($component, '/') . '\b/',
            '\\Filament\\Forms\\Components\\' . $component,
            $content
        );
    }

    // 3. Fix Tables\Actions\* -> \Filament\Tables\Actions\* doesn't exist, use Filament\Actions\*
    $tableActions = [
        'ViewAction',
        'EditAction',
        'DeleteAction',
        'BulkActionGroup',
        'DeleteBulkAction',
        'BulkAction',
        'Action',
        'CreateAction',
        'ForceDeleteAction',
        'RestoreAction',
        'ForceDeleteBulkAction',
        'RestoreBulkAction',
    ];
    foreach ($tableActions as $action) {
        $content = str_replace(
            'Tables\\Actions\\' . $action . '::',
            '\\Filament\\Actions\\' . $action . '::',
            $content
        );
    }

    // 4. Fix Tables\Columns\BadgeColumn -> TextColumn with ->badge() since BadgeColumn is removed in v5
    $content = str_replace(
        'Tables\\Columns\\BadgeColumn::',
        '\\Filament\\Tables\\Columns\\TextColumn::',
        $content
    );

    // 5. Fix Infolists namespace if present
    $content = str_replace(
        'use Filament\\Infolists\\Components\\Section;',
        'use Filament\\Infolists\\Components\\Section as InfolistSection;',
        $content
    );

    // 6. Fix form() calls inside action that still use Forms\Components inline
    // (handled by step 2 above)

    file_put_contents($res['file'], $content);
    echo "Fixed: {$res['file']}\n";
}

echo "\nDone!\n";
