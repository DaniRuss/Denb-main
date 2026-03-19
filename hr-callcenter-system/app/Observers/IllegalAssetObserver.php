<?php

namespace App\Observers;

use App\Models\IllegalAsset;

class IllegalAssetObserver
{
    /**
     * Handle the IllegalAsset "created" event.
     */
    public function created(IllegalAsset $illegalAsset): void
    {
        \App\Models\AssetActivity::create([
            'illegal_asset_id' => $illegalAsset->id,
            'user_id' => \Illuminate\Support\Facades\Auth::id() ?? 1, // Fallback to system user if needed
            'action' => 'Registered',
            'description' => "Asset '{$illegalAsset->asset_type}' was registered at {$illegalAsset->location_found}.",
        ]);
    }

    /**
     * Handle the IllegalAsset "updated" event.
     */
    public function updated(IllegalAsset $illegalAsset): void
    {
        // Only log if general attributes were changed (status changes are logged by actions)
        if ($illegalAsset->wasChanged(['asset_type', 'description', 'location_found', 'date_confiscated'])) {
            \App\Models\AssetActivity::create([
                'illegal_asset_id' => $illegalAsset->id,
                'user_id' => \Illuminate\Support\Facades\Auth::id() ?? 1,
                'action' => 'Edited',
                'description' => "Asset details were updated.",
            ]);
        }
    }
}
