<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Edit Shop: {{ $shop->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg">
                <div class="p-6">
                    @if ($errors->any())
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                            <ul class="list-disc list-inside text-sm">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('shops.update', $shop) }}" method="POST">
                        @csrf
                        @method('PATCH')

                        <div class="mb-4">
                            <label for="name" class="block text-sm font-medium text-gray-700 mb-1">{{ __('Shop Name') }}</label>
                            <input
                                type="text"
                                id="name"
                                name="name"
                                value="{{ old('name', $shop->name) }}"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                required
                            >
                        </div>

                        <div class="mb-4">
                            <label for="webhook_url" class="block text-sm font-medium text-gray-700 mb-1">{{ __('Webhook URL') }}</label>
                            <input
                                type="url"
                                id="webhook_url"
                                name="webhook_url"
                                value="{{ old('webhook_url', $shop->getRawOriginal('webhook_url')) }}"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                placeholder="https://your-server.com/webhook"
                                required
                            >
                            <p class="text-sm text-gray-500 mt-1">{{ __('POST requests will be sent to this URL for each new order.') }}</p>
                        </div>

                        <div class="mb-4">
                            <label for="polling_interval_minutes" class="block text-sm font-medium text-gray-700 mb-1">{{ __('Polling Interval (minutes)') }}</label>
                            <input
                                type="number"
                                id="polling_interval_minutes"
                                name="polling_interval_minutes"
                                value="{{ old('polling_interval_minutes', $shop->polling_interval_minutes) }}"
                                min="{{ $minPollingInterval }}"
                                max="60"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                required
                            >
                            <p class="text-sm text-gray-500 mt-1">{{ __('How often to check for new orders (:min-60 minutes on your plan).', ['min' => $minPollingInterval]) }}</p>
                        </div>

                        <div class="mb-4">
                            <label class="flex items-center">
                                <input
                                    type="checkbox"
                                    name="active"
                                    value="1"
                                    {{ old('active', $shop->active) ? 'checked' : '' }}
                                    class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                                >
                                <span class="ml-2 text-sm text-gray-700">{{ __('Active (enable polling for this shop)') }}</span>
                            </label>
                        </div>

                        <div class="mb-6 border-t border-gray-100 pt-4">
                            @php $canUsePush = auth()->user()->tierAllowsPushNotifications(); @endphp
                            <label class="flex items-center {{ !$canUsePush ? 'opacity-50' : '' }}">
                                <input
                                    type="checkbox"
                                    name="push_notifications_enabled"
                                    value="1"
                                    {{ old('push_notifications_enabled', $shop->push_notifications_enabled) ? 'checked' : '' }}
                                    {{ !$canUsePush ? 'disabled' : '' }}
                                    class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                                >
                                <span class="ml-2 text-sm text-gray-700">{{ __('Enable push notifications for this shop') }}</span>
                            </label>
                            @if (!$canUsePush)
                                <p class="text-xs text-yellow-700 mt-1 ml-6">
                                    {{ __('Not available on your plan. Upgrade to Trial, Pro, or Business.') }}
                                </p>
                            @else
                                <p class="text-xs text-gray-500 mt-1 ml-6">{{ __('When enabled, a push notification is sent to your subscribed devices for each new order.') }}</p>
                            @endif
                        </div>

                        <div class="flex justify-between items-center">
                            <a href="{{ route('shops.show', $shop) }}" class="text-gray-600 hover:text-gray-900">{{ __('Cancel') }}</a>
                            <button
                                type="submit"
                                class="bg-indigo-600 text-white py-2 px-4 rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                            >
                                {{ __('Save Changes') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        @if (auth()->user()->tierAllowsPushNotifications())
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8 mt-6">
            <div class="bg-white shadow-sm sm:rounded-lg">
                <div class="p-6" x-data="deviceSubscription()">
                    <h3 class="text-base font-medium text-gray-900 mb-1">{{ __('Push Notifications on this Device') }}</h3>
                    <p class="text-sm text-gray-500 mb-4">{{ __('Subscribe or unsubscribe this browser/device from push notifications.') }}</p>

                    <div class="flex items-center justify-between">
                        <p class="text-sm text-gray-600" x-text="statusText()"></p>
                        <div>
                            <span x-show="!browserSupported" class="text-xs text-gray-400">{{ __('Not supported by this browser') }}</span>
                            <button
                                x-show="browserSupported"
                                @click="deviceSubscribed ? unsubscribeDevice() : subscribeDevice()"
                                :disabled="busy"
                                :class="deviceSubscribed
                                    ? 'bg-gray-200 text-gray-700 hover:bg-gray-300'
                                    : 'bg-indigo-600 text-white hover:bg-indigo-700'"
                                class="px-3 py-1.5 rounded text-sm disabled:opacity-50"
                                x-text="busy ? (deviceSubscribed ? labels.disabling : labels.enabling) : (deviceSubscribed ? labels.disable : labels.enable)"
                            ></button>
                        </div>
                    </div>

                    <template x-if="errorMessage">
                        <p class="text-xs text-red-600 mt-2" x-text="errorMessage"></p>
                    </template>
                </div>
            </div>
        </div>
        @endif
    </div>
</x-app-layout>

<script>
function deviceSubscription() {
    return {
        deviceSubscribed: false,
        browserSupported: false,
        busy: false,
        errorMessage: null,
        swRegistration: null,
        labels: {
            enable: @json(__('Enable on this device')),
            enabling: @json(__('Enabling...')),
            disable: @json(__('Disable on this device')),
            disabling: @json(__('Disabling...')),
        },

        async init() {
            this.browserSupported = ('serviceWorker' in navigator) && ('PushManager' in window);
            if (!this.browserSupported) return;

            try {
                await navigator.serviceWorker.register('/sw.js');
                this.swRegistration = await navigator.serviceWorker.ready;
                const subscription = await this.swRegistration.pushManager.getSubscription();
                if (subscription) {
                    const resp = await fetch('/push/check?endpoint=' + encodeURIComponent(subscription.endpoint));
                    const data = await resp.json();
                    this.deviceSubscribed = data.subscribed;
                }
            } catch (e) {
                console.error('SW init error', e);
            }
        },

        statusText() {
            if (!this.browserSupported) return '';
            if (Notification.permission === 'denied') return @json(__('Notifications are blocked. Please allow them in your browser settings.'));
            return this.deviceSubscribed
                ? @json(__('This device is subscribed and will receive push notifications.'))
                : @json(__('This device is not subscribed.'));
        },

        async subscribeDevice() {
            if (Notification.permission === 'denied') {
                this.errorMessage = 'Notifications are blocked in your browser settings.';
                return;
            }
            this.busy = true;
            this.errorMessage = null;
            try {
                const permission = await Notification.requestPermission();
                if (permission !== 'granted') {
                    this.errorMessage = 'Permission not granted.';
                    return;
                }
                const keyResp = await fetch('/push/vapid-public-key');
                const { public_key } = await keyResp.json();

                const subscription = await this.swRegistration.pushManager.subscribe({
                    userVisibleOnly: true,
                    applicationServerKey: this.urlBase64ToUint8Array(public_key),
                });

                await fetch('/push/subscribe', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                    body: JSON.stringify({
                        endpoint: subscription.endpoint,
                        keys: {
                            p256dh: btoa(String.fromCharCode(...new Uint8Array(subscription.getKey('p256dh')))),
                            auth: btoa(String.fromCharCode(...new Uint8Array(subscription.getKey('auth')))),
                        },
                    }),
                });

                this.deviceSubscribed = true;
            } catch (e) {
                this.errorMessage = 'Failed to subscribe: ' + e.message;
            } finally {
                this.busy = false;
            }
        },

        async unsubscribeDevice() {
            this.busy = true;
            this.errorMessage = null;
            try {
                const subscription = await this.swRegistration.pushManager.getSubscription();
                if (subscription) {
                    await fetch('/push/unsubscribe', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                        body: JSON.stringify({ endpoint: subscription.endpoint }),
                    });
                    await subscription.unsubscribe();
                }
                this.deviceSubscribed = false;
            } catch (e) {
                this.errorMessage = 'Failed to unsubscribe: ' + e.message;
            } finally {
                this.busy = false;
            }
        },

        urlBase64ToUint8Array(base64String) {
            const padding = '='.repeat((4 - base64String.length % 4) % 4);
            const base64 = (base64String + padding).replace(/-/g, '+').replace(/_/g, '/');
            const rawData = window.atob(base64);
            return Uint8Array.from([...rawData].map(char => char.charCodeAt(0)));
        },
    };
}
</script>
