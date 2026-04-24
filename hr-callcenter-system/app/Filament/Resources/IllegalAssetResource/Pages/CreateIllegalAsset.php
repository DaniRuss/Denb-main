<?php

namespace App\Filament\Resources\IllegalAssetResource\Pages;

use App\Filament\Resources\IllegalAssetResource;
use App\Models\IllegalAsset;
use App\Models\AssetActivity;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CreateIllegalAsset extends CreateRecord
{
    protected static string $resource = IllegalAssetResource::class;

    protected function handleRecordCreation(array $data): \Illuminate\Database\Eloquent\Model
    {
        $items = $data['items'] ?? [];
        unset($data['items']);

        // Explicitly set auto-filled fields since they are hidden on the create form
        $officer = \App\Models\Officer::where('user_id', Auth::id())->first();
        
        $data = array_merge($data, [
            'status' => 'Registered',
            'date_confiscated' => now(),
            'officer_id' => $officer?->id,
        ]);

        // If no repeater items (shouldn't happen with minItems=1), fall back to single record
        if (empty($items)) {
            $record = IllegalAsset::create($data);
            AssetActivity::create([
                'illegal_asset_id' => $record->id,
                'user_id' => Auth::id(),
                'action' => 'Registered',
                'description' => "Asset '{$record->asset_type}' registered for owner: {$record->owner_name}",
            ]);
            return $record;
        }

        $firstRecord = null;

        DB::transaction(function () use ($items, $data, &$firstRecord) {
            foreach ($items as $item) {
                $record = IllegalAsset::create(array_merge($data, [
                    'asset_type' => $item['asset_type'],
                    'quantity' => $item['quantity'] ?? 1,
                    'description' => $item['description'],
                ]));

                AssetActivity::create([
                    'illegal_asset_id' => $record->id,
                    'user_id' => Auth::id(),
                    'action' => 'Registered',
                    'description' => "Asset '{$item['asset_type']}' registered for owner: {$data['owner_name']}",
                ]);

                if (!$firstRecord) {
                    $firstRecord = $record;
                }
            }
        });

        return $firstRecord;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
