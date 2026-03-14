<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ $shop->name }}
            </h2>
            <a href="{{ route('shops.edit', $shop) }}" class="bg-indigo-600 text-white px-4 py-2 rounded-md text-sm hover:bg-indigo-700">
                Edit Settings
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                    {{ session('success') }}
                </div>
            @endif

            <!-- Shop Info -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Shop Information</h3>
                    <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Shop URL</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $shop->shop_url }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Status</dt>
                            <dd class="mt-1">
                                @if ($shop->active)
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Active</span>
                                @else
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Inactive</span>
                                @endif
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Webhook URL</dt>
                            <dd class="mt-1 text-sm text-gray-900 break-all">{{ $shop->webhook_url }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Polling Interval</dt>
                            <dd class="mt-1 text-sm text-gray-900">Every {{ $shop->polling_interval_minutes }} minutes</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Last Check</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $shop->last_order_check ? $shop->last_order_check->format('d/m/Y H:i:s') : 'Never' }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Total Webhooks Sent</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $shop->webhook_logs_count }}</dd>
                        </div>
                    </dl>
                </div>
            </div>

            <!-- Recent Webhooks -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-medium text-gray-900">Recent Webhooks</h3>
                        <a href="{{ route('shops.webhooks.index', $shop) }}" class="text-sm text-indigo-600 hover:text-indigo-500">View all</a>
                    </div>

                    @if ($recentWebhooks->isEmpty())
                        <p class="text-gray-500 text-sm">No webhooks sent yet.</p>
                    @else
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Order ID</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Sent At</th>
                                        <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    @foreach ($recentWebhooks as $webhook)
                                        <tr>
                                            <td class="px-4 py-2 text-sm text-gray-900">{{ Str::limit($webhook->order_id, 20) }}</td>
                                            <td class="px-4 py-2">
                                                @if ($webhook->status === 'sent')
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Sent</span>
                                                @elseif ($webhook->status === 'failed')
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Failed</span>
                                                @else
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">Pending</span>
                                                @endif
                                            </td>
                                            <td class="px-4 py-2 text-sm text-gray-500">{{ $webhook->sent_at ? $webhook->sent_at->diffForHumans() : '-' }}</td>
                                            <td class="px-4 py-2 text-right">
                                                <a href="{{ route('shops.webhooks.show', [$shop, $webhook]) }}" class="text-indigo-600 hover:text-indigo-900 text-sm">View</a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
