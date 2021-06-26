//Code taken from https://googlechrome.github.io/samples/service-worker/custom-offline-page/ (and edited)

const CACHE_NAME = 'offline';
const RESOURCES = ["offline.php", "manifest.webmanifest"];

self.addEventListener('install', (event) => {
  event.waitUntil((async () => {
    const cache = await caches.open(CACHE_NAME);
    for(let resource of RESOURCES){
      console.log(resource);
      await cache.add(new Request(resource, {cache: 'reload'}));
    }
    await fetch("resources/dist/assets-manifest.json")
      .then((response) => response.json())
      .then((manifest) => {
        console.log(manifest);
        const scriptsRequired = ["main.js", "src_table_engine_default_js.bundle.js"];
        scriptsRequired.map((scriptName) => {
          console.log(scriptName);
          cache.add(new Request("resources/dist/" + manifest[scriptName]["src"], {cache: 'reload'}));
        });
      });
  })());
});

self.addEventListener('activate', (event) => {
  event.waitUntil((async () => {
    // Enable navigation preload if it's supported.
    // See https://developers.google.com/web/updates/2017/02/navigation-preload
    if ('navigationPreload' in self.registration) {
      await self.registration.navigationPreload.enable();
    }
  })());

  // Tell the active service worker to take control of the page immediately.
  self.clients.claim();
});

self.addEventListener('fetch', (event) => {
  console.log(event);
  event.respondWith((async () => {
    try {
      // First, try to use the navigation preload response if it's supported.
      const preloadResponse = await event.preloadResponse;
      if (preloadResponse) {
        return preloadResponse;
      }

      const networkResponse = await fetch(event.request);
      return networkResponse;
    } catch (error) {
      // catch is only triggered if an exception is thrown, which is likely
      // due to a network error.
      // If fetch() returns a valid HTTP response with a response code in
      // the 4xx or 5xx range, the catch() will NOT be called.
      console.log('Fetch failed; returning offline page instead.', error);

      const cache = await caches.open(CACHE_NAME);
      let cache_element_name = event.request.url;
      if (event.request.mode === "navigate") {
        cache_element_name = "offline.php";
      }
      console.log('Cache element name:', cache_element_name);
      const cachedResponse = await cache.match(cache_element_name);
      return cachedResponse;
    }
  })());

  // If our if() condition is false, then this fetch handler won't intercept the
  // request. If there are any other fetch handlers registered, they will get a
  // chance to call event.respondWith(). If no fetch handlers call
  // event.respondWith(), the request will be handled by the browser as if there
  // were no service worker involvement.
});