const CACHE_NAME = 'llegamos-shell-v2';
const PUBLIC_ASSETS = ['/offline.html','/manifest.webmanifest','/icons/icon-192.png','/icons/icon-512.png','/icons/maskable-512.png'];
self.addEventListener('install', event => { event.waitUntil(caches.open(CACHE_NAME).then(cache => cache.addAll(PUBLIC_ASSETS)).then(() => self.skipWaiting())); });
self.addEventListener('activate', event => { event.waitUntil(caches.keys().then(keys => Promise.all(keys.filter(key => key !== CACHE_NAME).map(key => caches.delete(key)))).then(() => self.clients.claim())); });
self.addEventListener('fetch', event => {
  const request = event.request;
  if (request.method !== 'GET') return;
  const url = new URL(request.url);
  if (url.origin !== self.location.origin) return;
  if (request.mode === 'navigate') {
    event.respondWith(fetch(request).catch(() => caches.match('/offline.html')));
    return;
  }
  if (['style','script','image','font'].includes(request.destination) && (url.pathname.startsWith('/build/') || url.pathname.startsWith('/icons/'))) {
    event.respondWith(caches.match(request).then(cached => cached || fetch(request).then(response => { if (response.ok) caches.open(CACHE_NAME).then(cache => cache.put(request, response.clone())); return response; })));
  }
});
self.addEventListener('message', event => { if (event.data === 'SKIP_WAITING') self.skipWaiting(); });

self.addEventListener('push', event => {
  let data={title:'Llegamos',body:'Tienes una nueva notificacion.',url:'/dashboard',tag:'llegamos'};
  try { data={...data,...event.data.json()}; } catch (_) {}
  event.waitUntil(self.registration.showNotification(data.title,{body:data.body,icon:'/icons/icon-192.png',badge:'/icons/icon-192.png',tag:data.tag,data:{url:data.url},vibrate:[200,100,200],requireInteraction:data.tag?.startsWith('trip-offer')}));
});
self.addEventListener('notificationclick', event => { event.notification.close(); const url=new URL(event.notification.data?.url||'/dashboard',self.location.origin).href; event.waitUntil(clients.matchAll({type:'window',includeUncontrolled:true}).then(list=>{const open=list.find(c=>c.url===url);return open?open.focus():clients.openWindow(url);})); });