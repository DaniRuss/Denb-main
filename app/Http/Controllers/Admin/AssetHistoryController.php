<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\IllegalAsset;

class AssetHistoryController extends Controller
{
    /**
     * Display a printable view of the illegal asset's history.
     */
    public function print($id)
    {
        $asset = IllegalAsset::with(['activities.user', 'officer', 'subCity', 'woreda'])->findOrFail($id);
        return view('admin.illegal-assets.print-history', compact('asset'));
    }
}
