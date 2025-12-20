// BideshGomon Service Worker - Minimal Version
// This file prevents 404 errors while SW is disabled

self.addEventListener('install', (event) => {
  console.log('Service Worker: Installed (minimal version)');
  self.skipWaiting();
});

self.addEventListener('activate', (event) => {
  console.log('Service Worker: Activated (minimal version)');
  event.waitUntil(self.clients.claim());
});

// No caching or offline functionality in this minimal version
self.addEventListener('fetch', (event) => {
  // Pass through - no interception
  return;
});
