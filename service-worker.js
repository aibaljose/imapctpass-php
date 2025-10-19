// Basic service worker for ImpactPass
const CACHE_NAME = 'impactpass-v1';
const urlsToCache = [
  '/impactpass/',
  '/impactpass/index.php',
  '/impactpass/events.php',
  '/impactpass/css/index.css',
  '/impactpass/css/login.css'
];

// Install event - cache assets
self.addEventListener('install', event => {
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then(cache => {
        return cache.addAll(urlsToCache);
      })
  );
});

// Activate event - clean up old caches
self.addEventListener('activate', event => {
  event.waitUntil(
    caches.keys().then(cacheNames => {
      return Promise.all(
        cacheNames.filter(cacheName => {
          return cacheName.startsWith('impactpass-') && cacheName !== CACHE_NAME;
        }).map(cacheName => {
          return caches.delete(cacheName);
        })
      );
    })
  );
});

// Fetch event - serve from cache if available
self.addEventListener('fetch', event => {
  // Skip Razorpay-related requests and other external resources
  if (event.request.url.includes('razorpay.com') || event.request.url.includes('sentry.io')) {
    return;
  }

  const acceptHeader = event.request.headers.get('accept') || '';

  // For navigation/HTML requests use network-first strategy to avoid serving stale
  // pages that include session-specific content (login state).
  if (event.request.mode === 'navigate' || acceptHeader.includes('text/html')) {
    event.respondWith(
      fetch(event.request)
        .then(networkResponse => {
          // Update cache with latest HTML for offline fallback
          const responseClone = networkResponse.clone();
          caches.open(CACHE_NAME).then(cache => {
            cache.put(event.request, responseClone);
          });
          return networkResponse;
        })
        .catch(() => {
          // If network fails, fall back to cached HTML if available
          return caches.match(event.request).then(cached => {
            return cached || caches.match('/impactpass/index.php');
          });
        })
    );
    return;
  }

  // For other requests (static assets) keep cache-first behavior
  event.respondWith(
    caches.match(event.request).then(response => {
      if (response) return response;
      const fetchRequest = event.request.clone();
      return fetch(fetchRequest).then(networkResponse => {
        if (!networkResponse || networkResponse.status !== 200 || networkResponse.type !== 'basic') {
          return networkResponse;
        }
        const responseToCache = networkResponse.clone();
        caches.open(CACHE_NAME).then(cache => {
          cache.put(event.request, responseToCache);
        });
        return networkResponse;
      });
    })
  );
});