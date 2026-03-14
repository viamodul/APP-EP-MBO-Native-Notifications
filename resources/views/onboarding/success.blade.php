<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setup Complete - ePages Notifications</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center py-12 px-4">
    <div class="bg-white p-8 rounded-lg shadow-md w-full max-w-md text-center">
        <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
            <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
            </svg>
        </div>

        <h1 class="text-2xl font-bold text-gray-800 mb-2">Setup Complete!</h1>
        <p class="text-gray-600 mb-6">Your shop <strong>{{ $shop->name }}</strong> is now configured to receive order notifications.</p>

        <div class="bg-gray-50 rounded-md p-4 mb-6 text-left">
            <h2 class="text-sm font-medium text-gray-700 mb-2">Configuration Summary</h2>
            <dl class="text-sm space-y-1">
                <div class="flex justify-between">
                    <dt class="text-gray-500">Shop:</dt>
                    <dd class="text-gray-800 font-medium">{{ $shop->name }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-gray-500">Webhook URL:</dt>
                    <dd class="text-gray-800 font-medium truncate max-w-[200px]" title="{{ $shop->getRawOriginal('webhook_url') ?? 'Not configured' }}">
                        {{ $shop->getRawOriginal('webhook_url') ?? 'Not configured' }}
                    </dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-gray-500">Polling Interval:</dt>
                    <dd class="text-gray-800 font-medium">{{ $shop->polling_interval_minutes }} minutes</dd>
                </div>
            </dl>
        </div>

        @if (!$shop->getRawOriginal('webhook_url'))
            <div class="bg-yellow-50 border border-yellow-200 rounded-md p-4 mb-6 text-left">
                <div class="flex">
                    <svg class="h-5 w-5 text-yellow-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                    </svg>
                    <p class="text-sm text-yellow-700">
                        <strong>Webhook not configured.</strong> Go to the dashboard to set up your webhook URL and start receiving order notifications.
                    </p>
                </div>
            </div>
        @endif

        <div class="space-y-3">
            @if ($returnUrl)
                <a
                    href="{{ $returnUrl }}"
                    class="inline-block w-full bg-indigo-600 text-white py-2 px-4 rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                >
                    Return to your Shop
                </a>
            @else
                <p class="text-sm text-gray-500">You can close this window and return to your shop.</p>
            @endif
        </div>
    </div>
</body>
</html>
