const CACHE_NAME = 'livechat-pwa-v1';
const STATIC_ASSETS = [
    '/',
    '/favicon.ico',
    '/manifest.json',
    '/icons/icon-192.png',
    '/icons/icon-512.png'
];

// Install Event — Cache Core Assets
self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => {
            return cache.addAll(STATIC_ASSETS).catch(() => {});
        })
    );
    self.skipWaiting();
});

// Activate Event — Cleanup Old Caches
self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((keys) => {
            return Promise.all(
                keys.filter((key) => key !== CACHE_NAME).map((key) => caches.delete(key))
            );
        })
    );
    self.clients.claim();
});

// Fetch Event — Network First strategy (ensures latest live code is loaded)
self.addEventListener('fetch', (event) => {
    // Only intercept GET requests
    if (event.request.method !== 'GET') return;
    
    // Skip API or websocket requests
    const url = new URL(event.request.url);
    if (url.pathname.startsWith('/api') || url.pathname.startsWith('/broadcasting')) return;

    // Skip AJAX/JSON polling requests (e.g. /agent/alerts/poll?after=123,
    // /agent/team/unread-summary) — each poll has a different query string,
    // so caching these would grow the Cache Storage forever with responses
    // that are never read back and never evicted.
    if ((event.request.headers.get('accept') || '').includes('application/json')) return;

    event.respondWith(
        fetch(event.request)
            .then((response) => {
                // Update cache with new version if successful
                if (response && response.status === 200 && response.type === 'basic') {
                    const responseToCache = response.clone();
                    caches.open(CACHE_NAME).then((cache) => {
                        cache.put(event.request, responseToCache);
                    });
                }
                return response;
            })
            .catch(() => {
                // Fallback to cache if network fails (offline support)
                return caches.match(event.request);
            })
    );
});

// Push Notification Event (for WebPush / Background Notifications)
self.addEventListener('push', (event) => {
    let data = { title: 'Live Chat Alert', body: 'New live chat notification', url: '/agent/chats' };
    if (event.data) {
        try {
            data = event.data.json();
        } catch (e) {
            data.body = event.data.text();
        }
    }

    const options = {
        body: data.body,
        icon: '/icons/icon-192.png',
        badge: '/icons/icon-192.png',
        vibrate: [200, 100, 200],
        data: { url: data.url || '/agent/chats' },
        tag: 'livechat-push-' + Date.now()
    };

    event.waitUntil(
        self.registration.showNotification(data.title, options)
    );
});

// Notification Click Event — Focus existing tab or open new app window
self.addEventListener('notificationclick', (event) => {
    event.notification.close();

    const targetUrl = event.notification.data ? event.notification.data.url : '/agent/chats';

    event.waitUntil(
        clients.matchAll({ type: 'window', includeUncontrolled: true }).then((windowClients) => {
            for (let client of windowClients) {
                if (client.url.includes('/agent/') && 'focus' in client) {
                    client.navigate(targetUrl);
                    return client.focus();
                }
            }
            if (clients.openWindow) {
                return clients.openWindow(targetUrl);
            }
        })
    );
});
