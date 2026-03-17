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

            @if (session('error'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                    {{ session('error') }}
                </div>
            @endif

            {{-- Auto-deactivated shop warning --}}
            @if ($shop->wasAutoDeactivated())
                <div class="bg-red-50 border-l-4 border-red-400 p-4 rounded">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3 flex-1">
                            <h3 class="text-sm font-medium text-red-800">Shop Auto-Deactivated</h3>
                            <p class="mt-1 text-sm text-red-700">
                                This shop was automatically deactivated on {{ $shop->deactivated_at->format('d/m/Y H:i') }}
                                due to consecutive API failures.
                            </p>
                            @if ($shop->api_failure_reason)
                                <p class="mt-1 text-sm text-red-600">
                                    <strong>Reason:</strong> {{ $shop->api_failure_reason }}
                                </p>
                            @endif
                            <div class="mt-3">
                                <form action="{{ route('shops.reactivate', $shop) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="bg-red-600 text-white px-4 py-2 rounded-md text-sm hover:bg-red-700">
                                        Test Connection & Reactivate
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            @elseif ($shop->api_failure_count > 0)
                <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 rounded">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-yellow-800">API Connection Issues</h3>
                            <p class="mt-1 text-sm text-yellow-700">
                                This shop has {{ $shop->api_failure_count }} consecutive API failure(s).
                                After 3 failures, the shop will be automatically deactivated.
                            </p>
                            @if ($shop->api_failure_reason)
                                <p class="mt-1 text-sm text-yellow-600">
                                    <strong>Last error:</strong> {{ $shop->api_failure_reason }}
                                </p>
                            @endif
                        </div>
                    </div>
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
