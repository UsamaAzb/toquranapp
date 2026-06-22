<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;

class PushServiceWorkerController extends Controller
{
    public function __invoke(): Response
    {
        $script = <<<'JS'
const DEFAULT_ICON = '/pwa/icons/toquran-icon-192.png?v=20260623-icon-padding';
const DEFAULT_BADGE = '/pwa/icons/toquran-notification-badge.png?v=20260623-icon-padding';
const ALLOWED_EXACT_PATHS = ['/', '/login', '/students'];
const ALLOWED_PATH_PREFIXES = ['/student/', '/parent/'];

function toAllowedSameOriginUrl(rawUrl) {
  try {
    const url = new URL(rawUrl || '/', self.location.origin);
    if (url.origin !== self.location.origin) return self.location.origin + '/';
    const allowed = ALLOWED_EXACT_PATHS.includes(url.pathname)
      || ALLOWED_PATH_PREFIXES.some((prefix) => url.pathname.startsWith(prefix));
    return allowed ? url.href : self.location.origin + '/';
  } catch (_) {
    return self.location.origin + '/';
  }
}

self.addEventListener('push', (event) => {
  let payload = {};

  try {
    payload = event.data ? event.data.json() : {};
  } catch (_) {
    payload = {};
  }

  const title = payload.title || 'To Quran';
  const options = {
    body: payload.body || 'You have a new To Quran update.',
    icon: payload.icon || DEFAULT_ICON,
    badge: payload.badge || DEFAULT_BADGE,
    tag: payload.tag || undefined,
    data: {
      url: toAllowedSameOriginUrl(payload.url || '/')
    }
  };

  event.waitUntil(self.registration.showNotification(title, options));
});

self.addEventListener('notificationclick', (event) => {
  event.notification.close();
  const targetUrl = toAllowedSameOriginUrl(event.notification?.data?.url || '/');

  event.waitUntil((async () => {
    const windows = await clients.matchAll({ type: 'window', includeUncontrolled: true });
    for (const client of windows) {
      if ('focus' in client) {
        await client.focus();
        if ('navigate' in client) return client.navigate(targetUrl);
        return;
      }
    }

    if (clients.openWindow) {
      return clients.openWindow(targetUrl);
    }
  })());
});
JS;

        return response($script, 200, [
            'Content-Type' => 'application/javascript; charset=UTF-8',
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Service-Worker-Allowed' => '/',
        ]);
    }
}
