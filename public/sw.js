/**
 * Service worker for the Neral Gram Panchayat portal.
 *
 * This site shows citizens their outstanding tax and takes payments, so the
 * caching rules are deliberately conservative:
 *
 *  - HTML is NEVER stored. Caching a rendered page would mean a stale balance,
 *    an expired CSRF token (every form then fails with a 419), or - on a shared
 *    phone - one citizen's authenticated page shown to the next person.
 *  - Only fingerprinted/static assets are cached, and only same-origin ones.
 *  - Anything that is not a GET is passed straight through, so payments,
 *    logins and grievance submissions always hit the network.
 *
 * The single offline affordance is a fallback page shown when a navigation
 * fails with no connection.
 */

const VERSION = 'neralgov-v2';
const STATIC_CACHE = `${VERSION}-static`;
const OFFLINE_URL = '/offline.html';

const PRECACHE = [
    OFFLINE_URL,
    '/icons/icon-192.png',
    '/icons/icon-512.png',
];

/** Paths that must always be fetched live, never served from cache. */
const NEVER_CACHE = [
    '/admin',
    '/citizen',
    '/payment',
    '/grievance/submit',
    '/language',
];

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(STATIC_CACHE)
            .then((cache) => cache.addAll(PRECACHE))
            .then(() => self.skipWaiting())
    );
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys()
            .then((keys) => Promise.all(
                keys.filter((key) => !key.startsWith(VERSION))
                    .map((key) => caches.delete(key))
            ))
            .then(() => self.clients.claim())
    );
});

/**
 * Static assets are content-addressed or change rarely, so they are safe to
 * serve from cache. Everything else is not.
 */
function isCacheableAsset(url) {
    if (url.origin !== self.location.origin) {
        return false;
    }

    if (NEVER_CACHE.some((prefix) => url.pathname.startsWith(prefix))) {
        return false;
    }

    return /^\/(css|js|icons|build|images|fonts)\//.test(url.pathname)
        || /\.(css|js|png|jpe?g|svg|gif|webp|woff2?|ttf|ico)$/i.test(url.pathname);
}

self.addEventListener('fetch', (event) => {
    const request = event.request;

    // Payments, logins, form posts: never intercepted.
    if (request.method !== 'GET') {
        return;
    }

    const url = new URL(request.url);

    // Navigations go to the network every time. On failure we show the offline
    // page rather than a stale balance.
    if (request.mode === 'navigate') {
        event.respondWith(
            fetch(request).catch(() => caches.match(OFFLINE_URL))
        );
        return;
    }

    if (!isCacheableAsset(url)) {
        return;
    }

    event.respondWith(
        caches.match(request).then((cached) => {
            const network = fetch(request).then((response) => {
                if (response && response.ok && response.type === 'basic') {
                    const copy = response.clone();
                    caches.open(STATIC_CACHE).then((cache) => cache.put(request, copy));
                }
                return response;
            }).catch(() => cached);

            return cached || network;
        })
    );
});
