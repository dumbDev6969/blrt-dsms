// Register the PWA service worker.
// /sw.js lives in public/ and is NOT processed by Vite — the path is stable.
if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        navigator.serviceWorker
            .register('/sw.js')
            .then((registration) => {
                console.log('[SW] Registered, scope:', registration.scope);
            })
            .catch((error) => {
                console.warn('[SW] Registration failed:', error);
            });
    });
}
