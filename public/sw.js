const CACHE_NAME = 'istana-erp-v1';
const urlsToCache = [
  '/manifest.json',
  '/favicon.ico'
];

self.addEventListener('install', event => {
  event.waitUntil(
    caches.open(CACHE_NAME).then(cache => cache.addAll(urlsToCache))
  );
});

self.addEventListener('fetch', event => {
  // Pass through network requests with fallback
  event.respondWith(
    fetch(event.request).catch(() => caches.match(event.request))
  );
});
