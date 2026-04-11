<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Admin Dashboard
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">

            {{-- Stats --}}
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="bg-white rounded-lg shadow p-5 text-center">
                    <div class="text-3xl font-bold text-indigo-600">{{ $stats['total_users'] }}</div>
                    <div class="text-sm text-gray-500 mt-1">Total Users</div>
                </div>
                <div class="bg-white rounded-lg shadow p-5 text-center">
                    <div class="text-3xl font-bold text-indigo-600">{{ $stats['total_shops'] }}</div>
                    <div class="text-sm text-gray-500 mt-1">Total Shops</div>
                </div>
                <div class="bg-white rounded-lg shadow p-5 text-center">
                    <div class="text-3xl font-bold text-green-600">{{ $stats['active_shops'] }}</div>
                    <div class="text-sm text-gray-500 mt-1">Active Shops</div>
                </div>
                <div class="bg-white rounded-lg shadow p-5 text-center">
                    <div class="text-3xl font-bold text-blue-600">{{ $stats['total_webhooks'] }}</div>
                    <div class="text-sm text-gray-500 mt-1">Webhooks (period)</div>
                </div>
            </div>

            {{-- Users Table --}}
            <div class="bg-white shadow rounded-lg overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-800">Users ({{ $users->count() }})</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Email</th>
                                <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Tier</th>
                                <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Shops</th>
                                <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Webhooks</th>
                                <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Trial ends</th>
                                <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Registered</th>
                                <th class="px-4 py-3"></th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-100">
                            @forelse($users as $user)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 font-medium text-gray-900">{{ $user->name }}</td>
                                <td class="px-4 py-3 text-gray-600">{{ $user->email }}</td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium
                                        @if($user->subscription_tier === 'pro') bg-indigo-100 text-indigo-800
                                        @elseif($user->subscription_tier === 'business') bg-purple-100 text-purple-800
                                        @elseif($user->subscription_tier === 'starter') bg-blue-100 text-blue-800
                                        @elseif($user->subscription_tier === 'dev') bg-yellow-100 text-yellow-800
                                        @else bg-gray-100 text-gray-700
                                        @endif">
                                        {{ ucfirst($user->subscription_tier ?? 'trial') }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-gray-700">{{ $user->shops_count }}</td>
                                <td class="px-4 py-3 text-gray-700">
                                    {{ $user->webhooks_sent_this_period }}
                                    @if($user->getWebhooksLimit())
                                        <span class="text-gray-400">/ {{ $user->getWebhooksLimit() }}</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-gray-600">
                                    @if($user->trial_ends_at)
                                        {{ $user->trial_ends_at->format('d M Y') }}
                                        @if($user->trial_ends_at->isPast())
                                            <span class="text-red-500 text-xs ml-1">expired</span>
                                        @else
                                            <span class="text-green-600 text-xs ml-1">{{ $user->trial_ends_at->diffForHumans() }}</span>
                                        @endif
                                    @else
                                        <span class="text-gray-400">—</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-gray-500">{{ $user->created_at->format('d M Y') }}</td>
                                <td class="px-4 py-3">
                                    <a href="{{ route('admin.users.show', $user) }}" class="text-indigo-600 hover:text-indigo-800 text-xs font-medium">View →</a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="px-4 py-6 text-center text-gray-400">No users found.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Shops Table --}}
            <div class="bg-white shadow rounded-lg overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-800">Shops ({{ $shops->count() }})</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Shop</th>
                                <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Owner</th>
                                <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Push</th>
                                <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Polling</th>
                                <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Last check</th>
                                <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">API failures</th>
                                <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Added</th>
                                <th class="px-4 py-3"></th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-100">
                            @forelse($shops as $shop)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3">
                                    <div class="font-medium text-gray-900">{{ $shop->name }}</div>
                                    <div class="text-xs text-gray-400 truncate max-w-xs">{{ $shop->shop_url }}</div>
                                </td>
                                <td class="px-4 py-3 text-gray-600">
                                    @if($shop->user)
                                        <div>{{ $shop->user->name }}</div>
                                        <div class="text-xs text-gray-400">{{ $shop->user->email }}</div>
                                    @else
                                        <span class="text-gray-400">—</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    @if($shop->active)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">Active</span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800">Inactive</span>
                                        @if($shop->api_failure_reason)
                                            <div class="text-xs text-red-400 mt-0.5">{{ Str::limit($shop->api_failure_reason, 40) }}</div>
                                        @endif
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    @if($shop->push_notifications_enabled)
                                        <span class="text-green-600">✓</span>
                                    @else
                                        <span class="text-gray-300">—</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-gray-700">{{ $shop->polling_interval_minutes }}m</td>
                                <td class="px-4 py-3 text-gray-500">
                                    {{ $shop->last_order_check ? $shop->last_order_check->diffForHumans() : '—' }}
                                </td>
                                <td class="px-4 py-3">
                                    @if($shop->api_failure_count > 0)
                                        <span class="text-red-600 font-medium">{{ $shop->api_failure_count }}</span>
                                    @else
                                        <span class="text-gray-400">0</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-gray-500">{{ $shop->created_at->format('d M Y') }}</td>
                                <td class="px-4 py-3">
                                    <a href="{{ route('shops.show', $shop) }}" class="text-indigo-600 hover:text-indigo-800 text-xs font-medium">View →</a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="px-4 py-6 text-center text-gray-400">No shops found.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Queued Jobs --}}
            <div class="bg-white shadow rounded-lg overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-800">Queued Jobs ({{ $queuedJobs->count() }})</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Job</th>
                                <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Shop</th>
                                <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Queue</th>
                                <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Attempts</th>
                                <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Available at</th>
                                <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Created at</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-100">
                            @forelse($queuedJobs as $job)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 font-medium text-gray-900">{{ $job->job_name }}</td>
                                <td class="px-4 py-3">
                                    @if($job->related_shop)
                                        <a href="{{ route('shops.show', $job->related_shop) }}" class="text-indigo-600 hover:text-indigo-800">{{ $job->related_shop->name }}</a>
                                        <div class="text-xs text-gray-400">ID {{ $job->related_shop->id }}</div>
                                    @else
                                        <span class="text-gray-400">—</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-gray-600">{{ $job->queue }}</td>
                                <td class="px-4 py-3 text-gray-700">{{ $job->attempts }}</td>
                                <td class="px-4 py-3 text-gray-500">{{ \Carbon\Carbon::createFromTimestamp($job->available_at)->diffForHumans() }}</td>
                                <td class="px-4 py-3 text-gray-500">{{ \Carbon\Carbon::createFromTimestamp($job->created_at)->format('d M Y H:i') }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="px-4 py-6 text-center text-gray-400">No jobs in queue.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Failed Jobs --}}
            <div class="bg-white shadow rounded-lg overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-800">
                        Failed Jobs ({{ $failedJobs->count() }})
                        @if($failedJobs->count() > 0)
                            <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-700">{{ $failedJobs->count() }} failed</span>
                        @endif
                    </h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Job</th>
                                <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Queue</th>
                                <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Exception</th>
                                <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Failed at</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-100">
                            @forelse($failedJobs as $job)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 font-medium text-gray-900">{{ $job->job_name }}</td>
                                <td class="px-4 py-3 text-gray-600">{{ $job->queue }}</td>
                                <td class="px-4 py-3 text-red-600 max-w-sm">
                                    <span title="{{ $job->exception }}">{{ Str::limit(Str::of($job->exception)->before("\n"), 80) }}</span>
                                </td>
                                <td class="px-4 py-3 text-gray-500 whitespace-nowrap">{{ \Carbon\Carbon::parse($job->failed_at)->format('d M Y H:i') }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="px-4 py-6 text-center text-gray-400">No failed jobs.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
