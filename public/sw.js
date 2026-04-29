
"use strict";

const CACHE_NAME = "offline-cache-v1";
const OFFLINE_URL = '/offline.html';

const filesToCache = [
    OFFLINE_URL
];

self.addEventListener("install", (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then((cache) => cache.addAll(filesToCache))
    );
    self.skipWaiting();
});

self.addEventListener("fetch", (event) => {
    const url = new URL(event.request.url);
    
    // Skip service worker for:
    // 1. Same-origin requests with query parameters
    // 2. Requests with X-Inertia header (Inertia partial reloads)
    // 3. POST/PUT/DELETE/PATCH requests (forms, logout, etc.)
    // 4. API requests
    if (
        (url.origin === location.origin && url.search) ||
        event.request.headers.get('X-Inertia') ||
        event.request.method !== 'GET' ||
        url.pathname.startsWith('/api/')
    ) {
        return; // Let the browser handle it directly
    }
    
    if (event.request.mode === 'navigate') {
        event.respondWith(
            fetch(event.request)
                .catch(() => {
                    return caches.match(OFFLINE_URL);
                })
        );
    } else {
        event.respondWith(
            caches.match(event.request)
                .then((response) => {
                    return response || fetch(event.request);
                })
        );
    }
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((cacheNames) => {
            return Promise.all(
                cacheNames.map((cacheName) => {
                    if (cacheName !== CACHE_NAME) {
                        return caches.delete(cacheName);
                    }
                })
            );
        })
    );
    self.clients.claim();
});