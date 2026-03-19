<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\IllegalAsset;
use App\Models\AssetHandover;

class IllegalAssetTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_an_illegal_asset()
    {
        $asset = IllegalAsset::create([
            'asset_type' => 'Vehicle',
            'description' => 'Confiscated seden without license plates',
            'location_found' => 'Downtown Square',
            'date_confiscated' => '2026-03-10',
            'status' => 'Registered',
        ]);

        $this->assertDatabaseHas('illegal_assets', [
            'id' => $asset->id,
            'asset_type' => 'Vehicle',
        ]);
    }

    public function test_asset_handover_updates_status()
    {
        $asset = IllegalAsset::create([
            'asset_type' => 'Electronics',
            'location_found' => 'Market',
            'date_confiscated' => '2026-03-12',
            'status' => 'Registered',
        ]);

        $handover = AssetHandover::create([
            'illegal_asset_id' => $asset->id,
            'handover_date' => now()->toDateString(),
            'notes' => 'Handed over securely',
        ]);

        $asset->update(['status' => 'Handed Over']);

        $this->assertDatabaseHas('illegal_assets', [
            'id' => $asset->id,
            'status' => 'Handed Over',
        ]);

        $this->assertDatabaseHas('asset_handovers', [
            'id' => $handover->id,
            'illegal_asset_id' => $asset->id,
        ]);
    }
}
