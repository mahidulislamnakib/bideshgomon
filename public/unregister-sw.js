// Service Worker Unregister Script
// This script ensures any previously registered service workers are removed
// and all caches are cleared to prevent stale content issues

(function() {
  'use strict';

  // Unregister all service workers
  if ('serviceWorker' in navigator) {
    navigator.serviceWorker.getRegistrations().then(function(registrations) {
      for (let registration of registrations) {
        registration.unregister().then(function(success) {
          if (success) {
            console.log('[SW Cleanup] Service worker unregistered successfully');
          }
        });
      }
    }).catch(function(error) {
      console.error('[SW Cleanup] Failed to unregister service workers:', error);
    });
  }

  // Clear all caches
  if ('caches' in window) {
    caches.keys().then(function(cacheNames) {
      return Promise.all(
        cacheNames.map(function(cacheName) {
          return caches.delete(cacheName).then(function() {
            console.log('[SW Cleanup] Cache deleted:', cacheName);
          });
        })
      );
    }).catch(function(error) {
      console.error('[SW Cleanup] Failed to clear caches:', error);
    });
  }
})();
