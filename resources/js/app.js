import './bootstrap';

// ── Denb Offline Layer ────────────────────────────────────────────────────────
// Boot the PWA service worker, IndexedDB store, and connection UI on every page.
// Import is conditional so it doesn't break the public-facing portal pages.
if (document.querySelector('[data-filament-panel]') || window.location.pathname.startsWith('/admin')) {
    Promise.all([
        import('/js/offline/offline-db.js'),
        import('/js/offline/offline-sync.js'),
        import('/js/offline/offline-ui.js'),
    ]).then(([db, sync, ui]) => {
        // Expose modules globally so Alpine.js components can use them
        window.DenbDB   = db;
        window.DenbSync = sync;
        window.DenbUI   = ui;

        // Boot the UI (registers SW, starts connection listener, shows pill)
        ui.boot();
    }).catch((err) => {
        console.warn('[Denb Offline] Failed to load offline modules:', err);
    });
}
