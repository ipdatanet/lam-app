// Service worker for "القرآن المبين" — caches the app shell (this HTML/CSS/JS
// file, manifest, icons) so the app itself loads with no network connection.
// Quran page text and reciter audio are handled separately inside the page
// via IndexedDB (see the app's own offline-download feature in Settings),
// so this worker deliberately leaves those cross-origin requests alone and
// just lets them pass straight through to the network / browser HTTP cache.

const CACHE_NAME = "qm-shell-v1";
const APP_SHELL = [
  "./",
  "./index.html",
  "./manifest.json",
  "./icon-192.png",
  "./icon-512.png"
];

self.addEventListener("install", (event) => {
  self.skipWaiting();
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then((cache) => cache.addAll(APP_SHELL))
      .catch((err) => console.warn("SW: app shell cache failed", err))
  );
});

self.addEventListener("activate", (event) => {
  event.waitUntil(
    caches.keys().then((keys) =>
      Promise.all(keys.filter((k) => k !== CACHE_NAME).map((k) => caches.delete(k)))
    )
  );
  self.clients.claim();
});

self.addEventListener("fetch", (event) => {
  const req = event.request;
  if (req.method !== "GET") return;

  const url = new URL(req.url);
  // Only manage same-origin requests (the app shell itself). Everything else
  // — Quran data, fonts, audio from other domains — passes through untouched;
  // the page's own IndexedDB-based offline download handles that layer.
  if (url.origin !== self.location.origin) return;

  event.respondWith(
    caches.match(req).then((cached) => {
      const network = fetch(req)
        .then((res) => {
          if (res && res.ok) {
            const clone = res.clone();
            caches.open(CACHE_NAME).then((cache) => cache.put(req, clone));
          }
          return res;
        })
        .catch(() => cached);
      return cached || network;
    })
  );
});
