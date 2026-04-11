<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Admin › User: {{ $user->name }}
            </h2>
            <a href="{{ route('admin.dashboard') }}" class="text-sm text-indigo-600 hover:text-indigo-800">← Back to Admin</a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            @if(session('success'))
                <div class="bg-green-50 border border-green-200 text-green-800 rounded-lg px-4 py-3 text-sm">
                    {{ session('success') }}
                </div>
            @endif

            {{-- Edit Form --}}
            <div class="bg-white shadow rounded-lg">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-800">User Details</h3>
                </div>
                <form method="POST" action="{{ route('admin.users.update', $user) }}" class="px-6 py-5 space-y-5">
                    @csrf
                    @method('PATCH')

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        {{-- Name --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Name</label>
                            <input type="text" name="name" value="{{ old('name', $user->name) }}"
                                class="w-full border-gray-300 rounded-md shadow-sm text-sm focus:ring-indigo-500 focus:border-indigo-500 @error('name') border-red-400 @enderror">
                            @error('name')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                        </div>

                        {{-- Email --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                            <input type="email" name="email" value="{{ old('email', $user->email) }}"
                                class="w-full border-gray-300 rounded-md shadow-sm text-sm focus:ring-indigo-500 focus:border-indigo-500 @error('email') border-red-400 @enderror">
                            @error('email')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                        </div>

                        {{-- Subscription Tier --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Subscription Tier</label>
                            <select name="subscription_tier"
                                class="w-full border-gray-300 rounded-md shadow-sm text-sm focus:ring-indigo-500 focus:border-indigo-500">
                                @foreach($tiers as $tier)
                                    <option value="{{ $tier }}" {{ old('subscription_tier', $user->subscription_tier) === $tier ? 'selected' : '' }}>
                                        {{ ucfirst($tier) }}
                                    </option>
                                @endforeach
                            </select>
                            @error('subscription_tier')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                        </div>

                        {{-- Trial ends at --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Trial ends at</label>
                            <input type="date" name="trial_ends_at"
                                value="{{ old('trial_ends_at', $user->trial_ends_at?->format('Y-m-d')) }}"
                                class="w-full border-gray-300 rounded-md shadow-sm text-sm focus:ring-indigo-500 focus:border-indigo-500">
                            @error('trial_ends_at')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <div class="flex justify-end pt-2">
                        <button type="submit"
                            class="px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            Save changes
                        </button>
                    </div>
                </form>
            </div>

            {{-- Read-only info --}}
            <div class="bg-white shadow rounded-lg">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-800">Account Info</h3>
                </div>
                <div class="px-6 py-5 grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                    <div>
                        <div class="text-gray-500">Registered</div>
                        <div class="font-medium text-gray-800">{{ $user->created_at->format('d M Y') }}</div>
                    </div>
                    <div>
                        <div class="text-gray-500">Email verified</div>
                        <div class="font-medium text-gray-800">
                            {{ $user->email_verified_at ? $user->email_verified_at->format('d M Y') : '—' }}
                        </div>
                    </div>
                    <div>
                        <div class="text-gray-500">Webhooks (period)</div>
                        <div class="font-medium text-gray-800">
                            {{ $user->webhooks_sent_this_period }}
                            @if($user->getWebhooksLimit())
                                <span class="text-gray-400">/ {{ $user->getWebhooksLimit() }}</span>
                            @else
                                <span class="text-gray-400">/ ∞</span>
                            @endif
                        </div>
                    </div>
                    <div>
                        <div class="text-gray-500">Billing period</div>
                        <div class="font-medium text-gray-800">
                            @if($user->billing_period_ends_at)
                                ends {{ $user->billing_period_ends_at->format('d M Y') }}
                            @else
                                —
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- Shops --}}
            <div class="bg-white shadow rounded-lg overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-800">Shops ({{ $user->shops_count }})</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Shop</th>
                                <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Push</th>
                                <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Polling</th>
                                <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Last check</th>
                                <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">API failures</th>
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
                                <td class="px-4 py-3">
                                    @if($shop->active)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">Active</span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800">Inactive</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    {{ $shop->push_notifications_enabled ? '✓' : '—' }}
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
                                <td class="px-4 py-3">
                                    <a href="{{ route('shops.show', $shop) }}" class="text-indigo-600 hover:text-indigo-800 text-xs font-medium">View →</a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="px-4 py-6 text-center text-gray-400">No shops found.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
