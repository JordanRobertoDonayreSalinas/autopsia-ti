const CACHE_NAME = 'autopsia-ti-pwa-v6';
const STATIC_ASSETS = [
  '/',
  '/usuario/monitoreo',
  '/usuario/monitoreo/crear-acta',
  '/favicon.png',
  '/favicon.ico',
  '/js/offline-db.js',
  'https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap',
  'https://unpkg.com/@alpinejs/collapse@3.x.x/dist/cdn.min.js',
  'https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js',
  'https://cdn.jsdelivr.net/npm/sweetalert2@11',
  'https://unpkg.com/html5-qrcode',
  'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js',
  'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css'
];

// Instalar Service Worker y precachear estáticos
self.addEventListener('install', (event) => {
  event.waitUntil(
    caches.open(CACHE_NAME).then((cache) => {
      console.log('[Service Worker] Pre-caching static assets v6');
      return cache.addAll(STATIC_ASSETS).catch((err) => console.warn('[Service Worker] Precache warning:', err));
    })
  );
  self.skipWaiting();
});

// Activar y limpiar cachés antiguos
self.addEventListener('activate', (event) => {
  event.waitUntil(
    caches.keys().then((keys) => {
      return Promise.all(
        keys.map((key) => {
          if (key !== CACHE_NAME) {
            console.log('[Service Worker] Removing old cache:', key);
            return caches.delete(key);
          }
        })
      );
    })
  );
  self.clients.claim();
});

// Fetch con timeout rápido (1500ms) para evitar congelamientos en redes móviles sin datos
function fetchWithTimeout(request, timeout = 1500) {
  return new Promise((resolve, reject) => {
    const timer = setTimeout(() => {
      reject(new Error('Network timeout - switching to offline cache'));
    }, timeout);

    fetch(request).then(
      (response) => {
        clearTimeout(timer);
        resolve(response);
      },
      (err) => {
        clearTimeout(timer);
        reject(err);
      }
    );
  });
}

// Interceptador de peticiones con respuesta diferenciada por tipo de asset (HTML vs CSS vs JS)
self.addEventListener('fetch', (event) => {
  const req = event.request;
  
  if (req.method !== 'GET' || req.url.includes('/login') || req.url.includes('/logout')) {
    return;
  }

  const url = req.url;
  const isHtml = (req.headers.get('accept') && req.headers.get('accept').includes('text/html')) || req.mode === 'navigate';
  const isCss = req.destination === 'style' || url.endsWith('.css') || url.includes('/css/') || url.includes('/build/assets/');
  const isJs = req.destination === 'script' || url.endsWith('.js') || url.includes('/js/') || url.includes('cdn.') || url.includes('unpkg.');

  // Si el dispositivo está sin internet o con datos apagados
  if (!navigator.onLine) {
    event.respondWith(
      caches.match(req).then(async (cached) => {
        if (cached) return cached;

        // Si es CSS y no está en caché específica, entregar el CSS de Vite almacenado en la PWA
        if (isCss) {
          const cache = await caches.open(CACHE_NAME);
          const keys = await cache.keys();
          const cssKey = keys.find(k => k.url.includes('.css'));
          if (cssKey) return cache.match(cssKey);
          return new Response('/* Offline CSS Fallback */', { headers: { 'Content-Type': 'text/css' } });
        }

        // Si es JS y no está en caché específica, entregar cualquier JS compilado
        if (isJs) {
          const cache = await caches.open(CACHE_NAME);
          const keys = await cache.keys();
          const jsKey = keys.find(k => k.url.includes('.js'));
          if (jsKey) return cache.match(jsKey);
          return new Response('/* Offline JS Fallback */', { headers: { 'Content-Type': 'application/javascript' } });
        }

        // Si es navegación HTML, devolver la vista guardada con diseño
        if (isHtml) {
          const fallbackPage = await caches.match('/usuario/monitoreo/crear-acta') || 
                               await caches.match('/usuario/monitoreo') || 
                               await caches.match('/');
          if (fallbackPage) return fallbackPage;
        }

        return new Response('Página Offline', { headers: { 'Content-Type': 'text/html; charset=utf-8' } });
      })
    );
    return;
  }

  // Si está Online, pedir a la red y guardar copia fresca en caché
  event.respondWith(
    fetchWithTimeout(req, 1500)
      .then((networkResponse) => {
        if (networkResponse && networkResponse.status === 200) {
          const resClone = networkResponse.clone();
          caches.open(CACHE_NAME).then((cache) => {
            cache.put(req, resClone);
          });
        }
        return networkResponse;
      })
      .catch(async () => {
        const cachedResponse = await caches.match(req);
        if (cachedResponse) return cachedResponse;

        if (isCss) {
          const cache = await caches.open(CACHE_NAME);
          const keys = await cache.keys();
          const cssKey = keys.find(k => k.url.includes('.css'));
          if (cssKey) return cache.match(cssKey);
        }

        if (isHtml) {
          return caches.match('/usuario/monitoreo/crear-acta') || caches.match('/usuario/monitoreo') || caches.match('/');
        }
      })
  );
});
