// service-worker.js — One Click PWA
const CACHE_NAME = 'oneclick-v2';

const PRECACHE_ASSETS = [
    '/images/title.png',
    '/offline.html',
];

// INSTALL
self.addEventListener('install', event => {
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then(cache => cache.addAll(PRECACHE_ASSETS))
            .then(() => self.skipWaiting())
    );
});

// ACTIVATE — bersihkan cache versi lama
self.addEventListener('activate', event => {
    event.waitUntil(
        caches.keys().then(keys =>
            Promise.all(keys.filter(k => k !== CACHE_NAME).map(k => caches.delete(k)))
        ).then(() => self.clients.claim())
    );
});

// FETCH
self.addEventListener('fetch', event => {
    const req = event.request;
    if (req.method !== 'GET') return;

    const url = new URL(req.url);
    if (url.origin !== location.origin) return;

    // 1. Navigation requests (buka halaman via browser address bar / reload)
    // Network first -> fallback ke offline.html
    if (req.mode === 'navigate') {
        event.respondWith(
            fetch(req).catch(() => caches.match('/offline.html'))
        );
        return;
    }

    // 2. Inertia requests / API / Dynamic data -> JANGAN PERNAH DICACHE!
    // Request dari Inertia memiliki header X-Inertia
    const isInertia = req.headers.get('x-inertia') !== null;
    const isApi = url.pathname.startsWith('/api/');
    
    if (isInertia || isApi) {
        // Langsung request ke server tanpa cache agar data auth & role selalu akurat
        return;
    }

    // 3. Hanya cache file aset statis (vite build, images, fonts, css, js)
    const isStaticAsset = 
        url.pathname.startsWith('/build/') ||
        url.pathname.startsWith('/images/') ||
        /\.(css|js|png|jpe?g|gif|svg|webp|ico|woff2?|ttf|eot)(\?.*)?$/i.test(url.pathname);

    if (isStaticAsset) {
        event.respondWith(
            caches.match(req).then(cached => {
                return cached || fetch(req).then(response => {
                    if (response && response.status === 200 && response.type === 'basic') {
                        const responseClone = response.clone();
                        caches.open(CACHE_NAME).then(cache => cache.put(req, responseClone));
                    }
                    return response;
                });
            })
        );
    }
});
