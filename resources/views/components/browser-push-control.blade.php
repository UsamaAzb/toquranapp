@props([
  'context' => 'general',
])

<div
  class="tq-browser-push-control d-none"
  data-browser-push-control
  data-context="{{ $context }}"
  data-config-url="{{ route('browser-push.config') }}"
  data-subscribe-url="{{ route('browser-push.subscriptions.store') }}"
>
  <div class="alert alert-light border d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
    <div class="min-w-0">
      <div class="fw-semibold d-flex align-items-center gap-2">
        <i class="ti tabler-bell-ringing text-primary"></i>
        Browser notifications
      </div>
      <div class="small text-muted" data-browser-push-status>
        Enable this device to receive To Quran updates.
      </div>
    </div>
    <div class="d-flex flex-wrap gap-2">
      <button type="button" class="btn btn-sm btn-primary" data-browser-push-enable>
        <i class="ti tabler-bell-plus me-1"></i>
        Enable
      </button>
    </div>
  </div>
</div>

@once
  @push('scripts')
    <script>
      (() => {
        const controls = Array.from(document.querySelectorAll('[data-browser-push-control]'));
        if (!controls.length) return;

        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

        const urlBase64ToUint8Array = (base64String) => {
          const padding = '='.repeat((4 - base64String.length % 4) % 4);
          const base64 = (base64String + padding).replace(/-/g, '+').replace(/_/g, '/');
          const rawData = window.atob(base64);
          const outputArray = new Uint8Array(rawData.length);

          for (let i = 0; i < rawData.length; i += 1) {
            outputArray[i] = rawData.charCodeAt(i);
          }

          return outputArray;
        };

        const jsonRequest = async (url, options = {}) => {
          const response = await fetch(url, {
            credentials: 'same-origin',
            headers: {
              'Accept': 'application/json',
              'Content-Type': 'application/json',
              'X-CSRF-TOKEN': csrfToken,
              ...(options.headers || {})
            },
            ...options
          });

          if (!response.ok) {
            throw new Error('Request failed');
          }

          return response.json();
        };

        const setStatus = (control, text, tone = 'muted') => {
          const status = control.querySelector('[data-browser-push-status]');
          if (!status) return;
          status.textContent = text;
          status.className = `small text-${tone}`;
        };

        const setButtons = (control, state) => {
          const enable = control.querySelector('[data-browser-push-enable]');

          enable?.classList.toggle('d-none', state !== 'available' && state !== 'denied');

          if (enable) {
            enable.disabled = state === 'unavailable' || state === 'denied';
          }
        };

        const getRegistration = async () => {
          const existing = await navigator.serviceWorker.getRegistration('/service-worker.js');
          return existing || navigator.serviceWorker.register('/service-worker.js', { scope: '/' });
        };

        const loadControl = async (control) => {
          if (!('Notification' in window) || !('serviceWorker' in navigator) || !('PushManager' in window)) {
            control.classList.remove('d-none');
            setStatus(control, 'This browser does not support installable notifications.', 'muted');
            setButtons(control, 'unavailable');
            return;
          }

          const config = await jsonRequest(control.dataset.configUrl, { method: 'GET' });
          control.dataset.publicKey = config.public_key || '';
          control.dataset.sendingEnabled = config.sending_enabled ? '1' : '0';
          control.classList.remove('d-none');

          if (!config.configured || !config.public_key) {
            setStatus(control, 'Notifications are not configured yet.', 'muted');
            setButtons(control, 'unavailable');
            return;
          }

          if (Notification.permission === 'denied') {
            setStatus(control, 'Notifications are blocked in this browser. Change browser settings to enable them.', 'danger');
            setButtons(control, 'denied');
            return;
          }

          const registration = await getRegistration();
          const subscription = await registration.pushManager.getSubscription();

          if (subscription) {
            control.classList.add('d-none');
            return;
          }

          setStatus(control, 'Enable this device to receive To Quran updates.', 'muted');
          setButtons(control, 'available');
        };

        const enableControl = async (control) => {
          const permission = await Notification.requestPermission();
          if (permission !== 'granted') {
            setStatus(control, 'Notifications were not allowed on this device.', permission === 'denied' ? 'danger' : 'muted');
            setButtons(control, permission === 'denied' ? 'denied' : 'available');
            return;
          }

          const registration = await getRegistration();
          let subscription = await registration.pushManager.getSubscription();

          if (!subscription) {
            subscription = await registration.pushManager.subscribe({
              userVisibleOnly: true,
              applicationServerKey: urlBase64ToUint8Array(control.dataset.publicKey)
            });
          }

          const subscriptionJson = subscription.toJSON();
          await jsonRequest(control.dataset.subscribeUrl, {
            method: 'POST',
            body: JSON.stringify({
              endpoint: subscription.endpoint,
              keys: subscriptionJson.keys,
              contentEncoding: PushManager.supportedContentEncodings?.includes('aes128gcm') ? 'aes128gcm' : 'aesgcm'
            })
          });

          await loadControl(control);
        };

        controls.forEach((control) => {
          control.querySelector('[data-browser-push-enable]')?.addEventListener('click', async () => {
            setStatus(control, 'Preparing notification permission...', 'muted');
            try {
              await enableControl(control);
            } catch (_) {
              setStatus(control, 'Could not enable notifications. Please try again.', 'danger');
              setButtons(control, 'available');
            }
          });

          loadControl(control).catch(() => {
            control.classList.remove('d-none');
            setStatus(control, 'Notifications could not be checked right now.', 'danger');
            setButtons(control, 'unavailable');
          });
        });
      })();
    </script>
  @endpush
@endonce
