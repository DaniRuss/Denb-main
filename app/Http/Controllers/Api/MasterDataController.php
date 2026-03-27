<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Models\SubCity;
use App\Models\Woreda;
use App\Models\AwarenessEngagement;
use Illuminate\Http\Request;

/**
 * MasterDataController — provides the downloadable "reference data" needed offline.
 *
 * Paramilitary phones call GET /api/offline/master-data on login/Wi-Fi
 * and cache the result in IndexedDB for use when offline.
 */
class MasterDataController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        // Active campaigns scoped to user's Woreda if available
        $campaignsQuery = Campaign::where('status', 'active')
            ->with('woreda:id,name_am');

        if ($user->woreda_id) {
            $campaignsQuery->where('woreda_id', $user->woreda_id);
        }

        $campaigns = $campaignsQuery->get()->map(fn($c) => [
            'id'          => $c->id,
            'name_am'     => $c->name_am,
            'category'    => $c->category,
            'sub_city_id' => $c->sub_city_id,
            'woreda_id'   => $c->woreda_id,
        ]);

        $subCities = SubCity::orderBy('name_am')->get(['id', 'name_am']);

        $woredas = Woreda::orderBy('name_am')->get(['id', 'name_am', 'sub_city_id']);

        $violationTypes = collect(AwarenessEngagement::violationLabels())
            ->map(fn($label, $key) => ['key' => $key, 'label' => $label])
            ->values();

        return response()->json([
            'cached_at'      => now()->toIso8601String(),
            'campaigns'      => $campaigns,
            'sub_cities'     => $subCities,
            'woredas'        => $woredas,
            'violation_types' => $violationTypes,
        ]);
    }
}
