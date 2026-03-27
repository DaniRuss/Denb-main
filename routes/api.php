<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\SyncController;
use App\Http\Controllers\Api\MasterDataController;

/*
|--------------------------------------------------------------------------
| Offline Sync API Routes — Paramilitary Field Officers
|--------------------------------------------------------------------------
| These routes are consumed by the PWA service worker and IndexedDB layer.
| They require Sanctum/session authentication (cookie-based from the panel).
|--------------------------------------------------------------------------
*/

Route::middleware(['auth:sanctum'])->prefix('offline')->group(function () {

    // GET /api/offline/master-data
    // Downloads campaigns, sub-cities, woredas, violation types for IndexedDB caching
    Route::get('/master-data', [MasterDataController::class, 'index'])
        ->name('api.offline.master-data');

    // POST /api/offline/sync
    // Receives a batch of locally-stored draft records and persists them server-side
    Route::post('/sync', [SyncController::class, 'sync'])
        ->name('api.offline.sync');
});
