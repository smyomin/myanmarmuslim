self.addEventListener('install', function () {
    self.skipWaiting();
});

self.addEventListener('activate', function (e) {
    e.waitUntil(self.clients.claim());
});

self.addEventListener('fetch', function (e) {
    if (e.request.method !== 'GET') return;
    if (e.request.mode === 'navigate') {
        e.respondWith(
            fetch(e.request).catch(function () {
                return new Response(
                    '<!DOCTYPE html><html><head><meta charset="UTF-8">' +
                    '<meta name="viewport" content="width=device-width, initial-scale=1">' +
                    '<title>Offline</title></head>' +
                    '<body style="font-family:Arial;text-align:center;padding:60px 20px;background:#111827;color:#fff">' +
                    '<h2>You are offline</h2><p>The admin panel needs an internet connection.</p>' +
                    '<button onclick="location.reload()" style="padding:12px 28px;border:0;border-radius:8px;background:#c9a84c;color:#fff;font-size:16px">Try Again</button>' +
                    '</body></html>',
                    { headers: { 'Content-Type': 'text/html; charset=UTF-8' } }
                );
            })
        );
    }
});