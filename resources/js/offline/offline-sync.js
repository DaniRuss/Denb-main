/**
 * offline-sync.js — Background sync engine for the Denb Outbox
 *
 * Responsibilities:
 *  1. Fetch + cache master data on login / when online
 *  2. On connection restore, push pending outbox records to /api/offline/sync
 *  3. Register Background Sync with the Service Worker (where supported)
 *  4. Expose window.DenbSync for use by Filament Outbox page
 */

import {
    getAllDrafts,
    updateDraftStatus,
    deleteDraft,
    saveMasterData,
} from './offline-db.js';

// ── Master Data Download ───────────────────────────────────────────────────────

export async function refreshMasterData() {
    try {
        const resp = await fetch('/api/offline/master-data', {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        });
        if (!resp.ok) throw new Error('HTTP ' + resp.status);
        const data = await resp.json();
        await saveMasterData(data);
        console.log('[Denb Offline] Master data cached at', data.cached_at);
        return data;
    } catch (err) {
        console.warn('[Denb Offline] Master data refresh failed (offline?):', err.message);
        return null;
    }
}

// ── Sync Engine ───────────────────────────────────────────────────────────────

export async function syncOutbox({ onProgress } = {}) {
    const drafts = (await getAllDrafts()).filter((d) => d._outbox_status === 'pending');
    if (drafts.length === 0) {
        console.log('[Denb Offline] Outbox empty, nothing to sync.');
        return { synced: 0, failed: 0 };
    }

    console.log(`[Denb Offline] Syncing ${drafts.length} draft(s)...`);

    // Mark all as "syncing"
    await Promise.all(drafts.map((d) => updateDraftStatus(d.local_uuid, 'syncing')));

    let synced = 0;
    let failed = 0;

    // Send in batches of 10 to avoid request size issues
    const BATCH_SIZE = 10;
    for (let i = 0; i < drafts.length; i += BATCH_SIZE) {
        const batch = drafts.slice(i, i + BATCH_SIZE);

        try {
            const resp = await fetch('/api/offline/sync', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify({ records: batch }),
            });

            if (!resp.ok) throw new Error('HTTP ' + resp.status);

            const result = await resp.json();

            for (const r of result.results) {
                if (r.status === 'synced' || r.status === 'skipped') {
                    await updateDraftStatus(r.local_uuid, 'synced');
                    // Remove from IndexedDB once confirmed on server — protect citizen privacy
                    await deleteDraft(r.local_uuid);
                    synced++;
                } else {
                    await updateDraftStatus(r.local_uuid, 'failed');
                    failed++;
                }
                onProgress && onProgress({ synced, failed, total: drafts.length });
            }
        } catch (err) {
            console.error('[Denb Offline] Batch sync error:', err);
            // Mark this batch as failed so user can retry
            await Promise.all(batch.map((d) => updateDraftStatus(d.local_uuid, 'failed')));
            failed += batch.length;
        }
    }

    console.log(`[Denb Offline] Sync complete. Synced: ${synced}, Failed: ${failed}`);
    return { synced, failed };
}

// ── Register Background Sync ──────────────────────────────────────────────────

export async function registerBackgroundSync() {
    if (!('serviceWorker' in navigator) || !('SyncManager' in window)) {
        console.warn('[Denb Offline] Background Sync not supported in this browser.');
        return;
    }
    try {
        const reg = await navigator.serviceWorker.ready;
        await reg.sync.register('outbox-sync');
        console.log('[Denb Offline] Background sync registered.');
    } catch (e) {
        console.warn('[Denb Offline] Background sync registration failed:', e);
    }
}

// ── Boot ──────────────────────────────────────────────────────────────────────

export function boot() {
    // 1. Register service worker
    if ('serviceWorker' in navigator) {
        navigator.serviceWorker.register('/sw.js').then((reg) => {
            console.log('[Denb Offline] Service worker registered, scope:', reg.scope);

            // Listen for messages from SW (e.g. TRIGGER_SYNC)
            navigator.serviceWorker.addEventListener('message', (event) => {
                if (event.data && event.data.type === 'TRIGGER_SYNC') {
                    syncOutbox().then(() => {
                        window.dispatchEvent(new CustomEvent('denb:sync-complete'));
                    });
                }
            });
        }).catch((err) => {
            console.warn('[Denb Offline] Service worker registration failed:', err);
        });
    }

    // 2. When connection restores, auto-sync
    window.addEventListener('online', async () => {
        console.log('[Denb Offline] Connection restored. Starting sync...');
        await registerBackgroundSync();
        // Also do immediate sync as fallback for browsers without Background Sync API
        const result = await syncOutbox();
        if (result.synced > 0) {
            window.dispatchEvent(new CustomEvent('denb:sync-complete', { detail: result }));
        }
        // Refresh master data while we're online
        await refreshMasterData();
    });

    // 3. On load if online, download master data
    if (navigator.onLine) {
        refreshMasterData();
    }
}

// Make available globally for Filament Blade components
window.DenbSync = { syncOutbox, refreshMasterData, registerBackgroundSync };
