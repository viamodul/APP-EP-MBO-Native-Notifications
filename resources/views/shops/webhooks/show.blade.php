<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Webhook Details
            </h2>
            <a href="{{ route('shops.webhooks.index', $shop) }}" class="text-indigo-600 hover:text-indigo-500 text-sm">
                &larr; Back to Webhooks
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                    {{ session('error') }}
                </div>
            @endif

            <!-- Webhook Info -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex justify-between items-start mb-4">
                        <h3 class="text-lg font-medium text-gray-900">Webhook Information</h3>
                        @if ($webhook->status === 'failed' && $webhook->retry_count < 3)
                            <form action="{{ route('shops.webhooks.retry', [$shop, $webhook]) }}" method="POST">
                                @csrf
                                <button type="submit" class="bg-orange-600 text-white px-4 py-2 rounded-md text-sm hover:bg-orange-700">
                                    Retry Webhook
                                </button>
                            </form>
                        @endif
                    </div>

                    <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Order ID</dt>
                            <dd class="mt-1 text-sm text-gray-900 font-mono">{{ $webhook->order_id }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Status</dt>
                            <dd class="mt-1">
                                @if ($webhook->status === 'sent')
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Sent</span>
                                @elseif ($webhook->status === 'failed')
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Failed</span>
                                @else
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">Pending</span>
                                @endif
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Webhook URL</dt>
                            <dd class="mt-1 text-sm text-gray-900 break-all">{{ $webhook->webhook_url }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Response Status</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $webhook->response_status ?? '-' }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Created</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $webhook->created_at->format('d/m/Y H:i:s') }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Sent At</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $webhook->sent_at ? $webhook->sent_at->format('d/m/Y H:i:s') : '-' }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Retry Count</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $webhook->retry_count }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Order Created At</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $webhook->order_created_at ? $webhook->order_created_at->format('d/m/Y H:i:s') : '-' }}</dd>
                        </div>
                    </dl>
                </div>
            </div>

            <!-- Response Body -->
            @if ($webhook->response_body)
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Response Body</h3>
                    <pre class="bg-gray-900 text-green-400 p-4 rounded-md overflow-x-auto text-sm">{{ $webhook->response_body }}</pre>
                </div>
            </div>
            @endif

            <!-- Webhook Payload -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Webhook Payload</h3>
                    <pre class="bg-gray-900 text-green-400 p-4 rounded-md overflow-x-auto text-sm">{{ json_encode($webhook->webhook_payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                </div>
            </div>

            <!-- Order Data -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Original Order Data</h3>
                    <pre class="bg-gray-900 text-green-400 p-4 rounded-md overflow-x-auto text-sm max-h-96">{{ json_encode($webhook->order_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
