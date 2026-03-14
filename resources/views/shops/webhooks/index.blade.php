<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Webhooks: {{ $shop->name }}
            </h2>
            <a href="{{ route('shops.show', $shop) }}" class="text-indigo-600 hover:text-indigo-500 text-sm">
                &larr; Back to Shop
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                    {{ session('error') }}
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <!-- Search -->
                    <div class="mb-4">
                        <form action="{{ route('shops.webhooks.index', $shop) }}" method="GET" class="flex gap-2">
                            @if($currentStatus)
                                <input type="hidden" name="status" value="{{ $currentStatus }}">
                            @endif
                            <input type="text"
                                   name="search"
                                   value="{{ $search ?? '' }}"
                                   placeholder="Search by Order Number or Order ID..."
                                   class="flex-1 max-w-sm rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                            <button type="submit" class="px-4 py-2 bg-indigo-600 text-white text-sm rounded-md hover:bg-indigo-700">
                                Search
                            </button>
                            @if($search ?? false)
                                <a href="{{ route('shops.webhooks.index', array_filter(['shop' => $shop->id, 'status' => $currentStatus])) }}"
                                   class="px-4 py-2 bg-gray-200 text-gray-700 text-sm rounded-md hover:bg-gray-300">
                                    Clear
                                </a>
                            @endif
                        </form>
                    </div>

                    <!-- Status Filters -->
                    <div class="mb-4 flex gap-2">
                        <a href="{{ route('shops.webhooks.index', array_filter(['shop' => $shop->id, 'search' => $search ?? null])) }}"
                           class="px-3 py-1 rounded-full text-sm {{ !$currentStatus ? 'bg-indigo-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }}">
                            All
                        </a>
                        <a href="{{ route('shops.webhooks.index', array_filter(['shop' => $shop->id, 'status' => 'sent', 'search' => $search ?? null])) }}"
                           class="px-3 py-1 rounded-full text-sm {{ $currentStatus === 'sent' ? 'bg-green-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }}">
                            Sent
                        </a>
                        <a href="{{ route('shops.webhooks.index', array_filter(['shop' => $shop->id, 'status' => 'failed', 'search' => $search ?? null])) }}"
                           class="px-3 py-1 rounded-full text-sm {{ $currentStatus === 'failed' ? 'bg-red-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }}">
                            Failed
                        </a>
                        <a href="{{ route('shops.webhooks.index', array_filter(['shop' => $shop->id, 'status' => 'pending', 'search' => $search ?? null])) }}"
                           class="px-3 py-1 rounded-full text-sm {{ $currentStatus === 'pending' ? 'bg-yellow-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }}">
                            Pending
                        </a>
                    </div>

                    @if ($webhooks->isEmpty())
                        <p class="text-gray-500">No webhooks found.</p>
                    @else
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Order</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Response</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Retries</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Created</th>
                                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    @foreach ($webhooks as $webhook)
                                        <tr>
                                            <td class="px-4 py-3 text-sm text-gray-900">
                                                <span class="font-semibold">{{ $webhook->order_data['orderNumber'] ?? '-' }}</span>
                                                <span class="text-gray-500 text-xs font-mono block">({{ Str::limit($webhook->order_id, 20) }})</span>
                                            </td>
                                            <td class="px-4 py-3">
                                                @if ($webhook->status === 'sent')
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Sent</span>
                                                @elseif ($webhook->status === 'failed')
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Failed</span>
                                                @else
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">Pending</span>
                                                @endif
                                            </td>
                                            <td class="px-4 py-3 text-sm text-gray-500">
                                                {{ $webhook->response_status ?? '-' }}
                                            </td>
                                            <td class="px-4 py-3 text-sm text-gray-500">
                                                {{ $webhook->retry_count }}
                                            </td>
                                            <td class="px-4 py-3 text-sm text-gray-500">
                                                {{ $webhook->created_at->format('d/m/Y H:i') }}
                                            </td>
                                            <td class="px-4 py-3 text-right text-sm space-x-2">
                                                <a href="{{ route('shops.webhooks.show', [$shop, $webhook]) }}" class="text-indigo-600 hover:text-indigo-900">View</a>
                                                @if ($webhook->status === 'failed' && $webhook->retry_count < 3)
                                                    <form action="{{ route('shops.webhooks.retry', [$shop, $webhook]) }}" method="POST" class="inline">
                                                        @csrf
                                                        <button type="submit" class="text-orange-600 hover:text-orange-900">Retry</button>
                                                    </form>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-4">
                            {{ $webhooks->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
