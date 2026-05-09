/**
 * BLRT-DSMS Service Worker
 *
 * Strategy:
 *  - Navigation requests  → Network-first, fallback to /offline
 *  - Vite build assets    → Cache-first (filenames are content-hashed)
 *  - Everything else      → Network-first, stale-while-revalidate fallback
 *
 * Livewire compatibility:
 *  - All non-GET requests are passed through untouched (Livewire uses POST)
 *  - /livewire/* paths are explicitly excluded from all caching
 */

const CACHE_NAME = 'blrt-dsms-v1';
const OFFLINE_URL = '/offline';

// ─── Install ──────────────────────────────────────────────────────────────────
// Pre-cache the offline fallback page so it's always available.
self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => cache.add(OFFLINE_URL))
    );
    // Activate immediately without waiting for old SW to be discarded.
    self.skipWaiting();
});

// ─── Activate ─────────────────────────────────────────────────────────────────
// Delete any caches from previous versions.
self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((keys) =>
            Promise.all(
                keys
                    .filter((key) => key !== CACHE_NAME)
                    .map((key) => caches.delete(key))
            )
        )
    );
    // Take control of all open clients immediately.
    self.clients.claim();
});

// ─── Fetch ────────────────────────────────────────────────────────────────────
self.addEventListener('fetch', (event) => {
    const { request } = event;
    const url = new URL(request.url);

    // 1. Pass through all non-GET requests without interception.
    //    This covers Livewire POST updates, form submissions, file uploads, etc.
    if (request.method !== 'GET') {
        return;
    }

    // 2. Explicitly skip Livewire endpoints to never cache stateful responses.
    //    Default endpoints: /livewire/update, /livewire/upload-file
    if (url.pathname.startsWith('/livewire/')) {
        return;
    }

    // 3. Skip browser extensions and cross-origin requests.
    if (!url.origin.startsWith(self.location.origin) && url.origin !== self.location.origin) {
        return;
    }

    // 4. Vite build assets → Cache-first.
    //    These have content-hashed filenames (e.g. app-BCTnOFwb.css),
    //    so a cache hit is always fresh. New deploys produce new filenames,
    //    which get cached on first request.
    if (url.pathname.startsWith('/build/')) {
        event.respondWith(
            caches.match(request).then((cached) => {
                if (cached) return cached;

                return fetch(request).then((response) => {
                    // Only cache valid, same-origin responses.
                    if (!response || response.status !== 200 || response.type !== 'basic') {
                        return response;
                    }
                    const clone = response.clone();
                    caches.open(CACHE_NAME).then((cache) => cache.put(request, clone));
                    return response;
                });
            })
        );
        return;
    }

    // 5. Static PWA assets → Cache-first.
    //    Icons and manifest are stable files.
    if (url.pathname.startsWith('/icons/') || url.pathname === '/manifest.json') {
        event.respondWith(
            caches.match(request).then((cached) => {
                if (cached) return cached;
                return fetch(request).then((response) => {
                    if (!response || response.status !== 200) return response;
                    const clone = response.clone();
                    caches.open(CACHE_NAME).then((cache) => cache.put(request, clone));
                    return response;
                });
            })
        );
        return;
    }

    // 6. Navigation requests → Network-first, fallback to offline page.
    //    Laravel handles routing server-side; we never want to serve a stale
    //    HTML page. Only fall back to /offline when the network is unreachable.
    if (request.mode === 'navigate') {
        event.respondWith(
            fetch(request).catch(() => caches.match(OFFLINE_URL))
        );
        return;
    }

    // 7. Everything else → Network-first with no caching.
    //    Catches API calls, images, fonts loaded at runtime, etc.
    event.respondWith(
        fetch(request).catch(() => caches.match(request))
    );
});
