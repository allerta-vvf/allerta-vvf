//import { CacheableResponsePlugin } from 'workbox-cacheable-response/CacheableResponsePlugin';
import { CacheFirst } from 'workbox-strategies/CacheFirst';
import { NetworkFirst } from 'workbox-strategies/NetworkFirst';
//import { ExpirationPlugin } from 'workbox-expiration/ExpirationPlugin';
//import { NavigationRoute } from 'workbox-routing/NavigationRoute';
import { precacheAndRoute } from 'workbox-precaching/precacheAndRoute';
import { registerRoute } from 'workbox-routing/registerRoute';

precacheAndRoute(self.__WB_MANIFEST);

registerRoute(
  new RegExp('.*\.js'),
  new NetworkFirst({
    cacheName: 'js-cache',
  })
);

registerRoute(
  new RegExp('\.{svg,jpg,png,gif,ico}$'),
  new CacheFirst({
    cacheName: 'images-cache',
  })
);

registerRoute(
  new RegExp('\.{eot,ttf,woff,woff2}$'),
  new CacheFirst({
    cacheName: 'fonts-cache',
  })
);