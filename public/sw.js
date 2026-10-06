/* Service worker minimal : rend l'application installable (écran d'accueil du téléphone).
   Les pages restent toujours chargées depuis le serveur (données à jour, pas de cache de pages). */
self.addEventListener('install', function () { self.skipWaiting(); });
self.addEventListener('activate', function (e) { e.waitUntil(self.clients.claim()); });
self.addEventListener('fetch', function () { /* réseau uniquement */ });
