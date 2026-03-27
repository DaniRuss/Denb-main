/**
 * Denb Field App — Service Worker
 * Implements Cache-First for static assets and Network-First for API/admin pages.
 * Falls back to /offline.html for navigation requests when offline.
 */

const CACHE_VERSION = 'denb-v1';
const OFFLINE_URL   = '/offline.html';

// Assets to pre-cache on install (shell caching)
const PRECACHE_ASSETS = [
    '/',
    '/offline.html',
    '/manifest.json',
];

// ─────────────────────────────────────────
// INSTALL — Pre-cache shell assets
// ─────────────────────────────────────────
self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_VERSION).then((cache) => {
            return cache.addAll(PRECACHE_ASSETS);
        }).then(() => self.skipWaiting())
    );
});

// ─────────────────────────────────────────
// ACTIVATE — Clean up old caches
// ─────────────────────────────────────────
self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((keys) =>
            Promise.all(
                keys.filter((k) => k !== CACHE_VERSION).map((k) => caches.delete(k))
            )
        ).then(() => self.clients.claim())
    );
});

// ─────────────────────────────────────────
// FETCH — Strategy: Network-First
// Falls back to cache, then /offline.html for navigations
// ─────────────────────────────────────────
self.addEventListener('fetch', (event) => {
    const { request } = event;
    const url = new URL(request.url);

    // Skip non-GET and cross-origin requests
    if (request.method !== 'GET' || url.origin !== location.origin) return;

    // Skip API POST calls (handled separately via background sync)
    if (url.pathname.startsWith('/api/')) return;

    event.respondWith(
        fetch(request)
            .then((networkResponse) => {
                // Cache successful navigation and static responses
                if (networkResponse.ok) {
                    const clone = networkResponse.clone();
                    caches.open(CACHE_VERSION).then((cache) => cache.put(request, clone));
                }
                return networkResponse;
            })
            .catch(() => {
                // Network failed — serve from cache
                return caches.match(request).then((cachedResponse) => {
                    if (cachedResponse) return cachedResponse;

                    // For navigation requests, show offline page
                    if (request.mode === 'navigate') {
                        return caches.match(OFFLINE_URL);
                    }
                    return new Response('Offline', { status: 503 });
                });
            })
    );
});

// ─────────────────────────────────────────
// BACKGROUND SYNC — "outbox-sync" tag
// Triggered automatically when connection restores
// ─────────────────────────────────────────
self.addEventListener('sync', (event) => {
    if (event.tag === 'outbox-sync') {
        event.waitUntil(syncOutbox());
    }
});

async function syncOutbox() {
    // Notify all open clients to run the sync
    const clients = await self.clients.matchAll({ type: 'window' });
    for (const client of clients) {
        client.postMessage({ type: 'TRIGGER_SYNC' });
    }
}

// ─────────────────────────────────────────
// MESSAGE — Handle manual trigger from UI
// ─────────────────────────────────────────
self.addEventListener('message', (event) => {
    if (event.data && event.data.type === 'SKIP_WAITING') {
        self.skipWaiting();
    }
});
