/**
 * offline-sync.js — Background sync engine for the Denb Outbox
 */

import {
    getAllDrafts,
    updateDraftStatus,
    updateDraftError,
    deleteDraft,
    saveMasterData,
} from './offline-db.js';

export async function refreshMasterData() {
    try {
        const resp = await fetch('/api/offline/master-data', {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        });
        if (!resp.ok) throw new Error('HTTP ' + resp.status);
        const data = await resp.json();
        await saveMasterData(data);
        console.log('[Denb Offline] Master data cached');
        return data;
    } catch (err) {
        console.warn('[Denb Offline] Master data refresh failed (offline?)');
        return null;
    }
}

export async function syncOutbox({ onProgress } = {}) {
    const drafts = (await getAllDrafts()).filter((d) => d._outbox_status === 'pending');
    if (drafts.length === 0) return { synced: 0, failed: 0 };

    await Promise.all(drafts.map((d) => updateDraftStatus(d.local_uuid, 'syncing')));

    let synced = 0;
    let failed = 0;
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
                    await deleteDraft(r.local_uuid);
                    synced++;
                } else {
                    await updateDraftStatus(r.local_uuid, 'failed');
                    failed++;
                }
                onProgress && onProgress({ synced, failed, total: drafts.length });
            }
        } catch (err) {
            await Promise.all(batch.map((d) => updateDraftStatus(d.local_uuid, 'failed')));
            failed += batch.length;
        }
    }
    return { synced, failed };
}

export async function registerBackgroundSync() {
    if (!('serviceWorker' in navigator) || !('SyncManager' in window)) return;
    try {
        const reg = await navigator.serviceWorker.ready;
        await reg.sync.register('outbox-sync');
    } catch (e) {
        console.warn('[Denb Offline] Background sync registration failed', e);
    }
}

export function boot() {
    if ('serviceWorker' in navigator) {
        navigator.serviceWorker.register('/sw.js').then((reg) => {
            console.log('[Denb Offline] Service worker registered, scope:', reg.scope);
            navigator.serviceWorker.addEventListener('message', (event) => {
                if (event.data && event.data.type === 'TRIGGER_SYNC') {
                    syncOutbox().then(() => {
                        window.dispatchEvent(new CustomEvent('denb:sync-complete'));
                    });
                }
            });
        });
    }

    window.addEventListener('online', async () => {
        await registerBackgroundSync();
        const result = await syncOutbox();
        if (result.synced > 0) {
            window.dispatchEvent(new CustomEvent('denb:sync-complete', { detail: result }));
        }
        await refreshMasterData();
    });

    if (navigator.onLine) {
        refreshMasterData();
    }
}

window.DenbSync = { syncOutbox, refreshMasterData, registerBackgroundSync };
