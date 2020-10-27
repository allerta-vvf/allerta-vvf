let cacheVersion = 1
let cacheName = "static-"+cacheVersion

const urls = ['/offline.php', '/resources/dist/main.js', '/resources/dist/maps.js', '/manifest.webmanifest', '/resources/images/favicon.ico', '/resources/dist/marker-icon.png', '/resources/dist/layers.png', '/resources/dist/layers-2x.png', '/resources/images/android-chrome-192x192.png', '/resources/images/android-chrome-384x384.png', '/resources/images/black_helmet.png', '/resources/images/red_helmet.png', '/resources/images/wheel.png', '/resources/images/logo.png', '/resources/images/owner.png', '/resources/dist/fonts/fontawesome-webfont.ttf', '/resources/dist/fonts/fontawesome-webfont.svg', '/resources/dist/fonts/fontawesome-webfont.woff', '/resources/dist/fonts/fontawesome-webfont.woff2'];

function fetchHandler(event, content_type, not_found_message){
    event.respondWith(
        fetch(event.request).then(function (response) {
            return response;
        }).catch(function (error) {
            if(content_type == null){ // if content_type is null, load a file from cache as not found response
                var not_found_response = caches.match(not_found_message).then(function(response) {
                    return response;
                });
            } else {
                var not_found_response = new Response(new Blob([not_found_message]), {
                    headers: {'Content-Type': content_type}
                });
            }
            return caches.match(event.request).then(function(response) {
                return response || not_found_response;
            });
        })
    );
}
self.addEventListener('fetch', function (event) {
    console.log(event.request);
    var request = event.request;

	// https://stackoverflow.com/a/49719964
	if (event.request.cache === 'only-if-cached' && event.request.mode !== 'same-origin') return;

	if (request.headers.get('Accept').includes('text/html')) {
		fetchHandler(event, null, "/offline.php");
    } else if (request.destination == "script") {
        fetchHandler(event, "application/javascript", "console.error('Script "+event.request.url+" not found');");
    } else if (request.destination == "image") {
        fetchHandler(event, null, "/resources/images/logo.png");
    } else if (request.destination == "font") {
        fetchHandler(event, null, null);
    } else if (request.destination == "manifest" || request.url.includes("manifest")) {
        fetchHandler(event, null, "/manifest.webmanifest");
    } else {
        event.respondWith(fetch(request));
    }
});

self.addEventListener('install', event => {
    self.skipWaiting();
    console.log("Service worker installed");
})

self.addEventListener('activate', event => {
    event.waitUntil(caches.open(cacheName)
    .then((openCache) => {
        return openCache.addAll(urls);
    })
    .catch(err => console.error(err)))
});