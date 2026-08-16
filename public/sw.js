const CACHE_NAME = 'autopsia-ti-pwa-v4';
const STATIC_ASSETS = [
  '/',
  '/usuario/monitoreo',
  '/usuario/monitoreo/create',
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
      console.log('[Service Worker] Caching static assets v4');
      return cache.addAll(STATIC_ASSETS).catch((err) => {
        console.warn('[Service Worker] Precache warning:', err);
      });
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

// Fetch con timeout rapido (1500ms) para evitar congelamientos en redes moviles sin datos
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

// Interceptador de peticiones con estrategia Cache First / Network Fallback ultra-rapido
self.addEventListener('fetch', (event) => {
  const req = event.request;
  
  if (req.method !== 'GET' || req.url.includes('/login') || req.url.includes('/logout')) {
    return;
  }

  // Si el dispositivo está sin internet o con datos apagados, buscar DIRECTO en cache sin esperar
  if (!navigator.onLine) {
    event.respondWith(
      caches.match(req).then(async (cached) => {
        if (cached) return cached;
        // Fallback garantizado para cualquier ruta de monitoreo/consultorio
        const fallbackPage = await caches.match('/usuario/monitoreo') || await caches.match('/usuario/monitoreo/create') || await caches.match('/');
        if (fallbackPage) return fallbackPage;
        return new Response('<h3 style="font-family:sans-serif;padding:20px;text-align:center;">Modo Campo Offline: Vuelva a la pantalla principal de Monitoreo para continuar evaluando.</h3>', {
          headers: { 'Content-Type': 'text/html; charset=utf-8' }
        });
      })
    );
    return;
  }

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
        if (cachedResponse) {
          return cachedResponse;
        }

        const fallbackPage = await caches.match('/usuario/monitoreo') || await caches.match('/usuario/monitoreo/create') || await caches.match('/');
        if (fallbackPage) return fallbackPage;
        return new Response('<h3 style="font-family:sans-serif;padding:20px;text-align:center;">Modo Campo Offline: Vuelva a la pantalla principal de Monitoreo para continuar evaluando.</h3>', {
          headers: { 'Content-Type': 'text/html; charset=utf-8' }
        });
      })
  );
});
