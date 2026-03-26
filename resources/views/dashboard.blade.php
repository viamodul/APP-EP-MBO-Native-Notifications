<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                    {{ session('success') }}
                </div>
            @endif

            {{-- Subscription Usage Widget --}}
            @php
                $user = auth()->user();
                $tierConfig = $user->getTierConfig();
                $usagePercentage = $user->getUsagePercentage();
                $webhooksLimit = $user->getWebhooksLimit();
                $webhooksSent = $user->webhooks_sent_this_period;
            @endphp

            <div class="mb-6 bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between">
                        <div class="flex items-center gap-4">
                            <div>
                                <div class="flex items-center gap-2">
                                    <span class="text-lg font-semibold text-gray-900">{{ $tierConfig['name'] }} Plan</span>
                                    @if ($user->isOnTrial())
                                        <span class="px-2 py-0.5 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                                            {{ $user->daysUntilTrialExpires() }} {{ $user->daysUntilTrialExpires() === 1 ? __('day left') : __('days left') }}
                                        </span>
                                    @elseif ($user->trialExpired())
                                        <span class="px-2 py-0.5 text-xs font-semibold rounded-full bg-red-100 text-red-800">
                                            {{ __('Trial Expired') }}
                                        </span>
                                    @endif
                                </div>
                                @if ($webhooksLimit !== null)
                                    <p class="text-sm text-gray-500 mt-1">
                                        {{ number_format($webhooksSent) }} / {{ number_format($webhooksLimit) }} {{ __('webhooks used') }}
                                    </p>
                                @else
                                    <p class="text-sm text-gray-500 mt-1">
                                        {{ number_format($webhooksSent) }} {{ __('webhooks sent (unlimited)') }}
                                    </p>
                                @endif
                            </div>
                        </div>

                        <div class="mt-4 md:mt-0 flex items-center gap-4">
                            @if ($webhooksLimit !== null)
                                <div class="w-32">
                                    @php
                                        $percentage = min(100, $usagePercentage ?? 0);
                                        $barColor = match(true) {
                                            $percentage >= 100 => 'bg-red-600',
                                            $percentage >= 90 => 'bg-orange-500',
                                            $percentage >= 75 => 'bg-yellow-500',
                                            default => 'bg-green-600',
                                        };
                                    @endphp
                                    <div class="w-full bg-gray-200 rounded-full h-2">
                                        <div class="{{ $barColor }} h-2 rounded-full transition-all duration-300"
                                             style="width: {{ $percentage }}%"></div>
                                    </div>
                                    <p class="text-xs text-gray-500 mt-1 text-center">{{ $percentage }}% {{ __('% used') }}</p>
                                </div>
                            @endif

                            <a href="{{ route('billing.index') }}"
                               class="inline-flex items-center px-3 py-1.5 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                                @if ($user->isOnTrial() || $user->trialExpired())
                                    {{ __('Upgrade') }}
                                @else
                                    {{ __('View Plans') }}
                                @endif
                            </a>
                        </div>
                    </div>

                    @if ($usagePercentage >= 100)
                        <div class="mt-4 p-3 bg-red-50 border border-red-200 rounded-md">
                            <div class="flex">
                                <svg class="h-5 w-5 text-red-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                </svg>
                                <p class="text-sm text-red-700">
                                    {{ __("You've reached your webhook limit. Upgrade your plan to continue receiving notifications.") }}
                                </p>
                            </div>
                        </div>
                    @elseif ($user->trialExpired())
                        <div class="mt-4 p-3 bg-yellow-50 border border-yellow-200 rounded-md">
                            <div class="flex">
                                <svg class="h-5 w-5 text-yellow-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                </svg>
                                <p class="text-sm text-yellow-700">
                                    {{ __('Your trial has expired. Choose a plan to continue using the service.') }}
                                </p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            @php
                $shopsWithoutWebhook = $shops->filter(fn($shop) => !$shop->getRawOriginal('webhook_url'));
            @endphp

            @if ($shopsWithoutWebhook->isNotEmpty())
                <div class="mb-4 bg-yellow-50 border border-yellow-200 text-yellow-800 px-4 py-3 rounded">
                    <div class="flex">
                        <svg class="h-5 w-5 text-yellow-400 mr-2 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                        </svg>
                        <div>
                            <p class="font-medium">{{ __('Webhook not configured') }}</p>
                            <p class="text-sm">
                                {{ $shopsWithoutWebhook->count() }} {{ Str::plural(__('shop'), $shopsWithoutWebhook->count()) }} {{ __('without webhook URL') }}:
                                @foreach ($shopsWithoutWebhook as $shop)
                                    <a href="{{ route('shops.edit', $shop) }}" class="underline">{{ $shop->name }}</a>{{ !$loop->last ? ', ' : '' }}
                                @endforeach
                            </p>
                        </div>
                    </div>
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">{{ __('Your Shops') }}</h3>

                    @if ($shops->isEmpty())
                        <p class="text-gray-500">{{ __("You don't have any shops connected yet.") }}</p>
                        <p class="text-sm text-gray-400 mt-2">{{ __('Install the app from the ePages App Store to connect a shop.') }}</p>
                    @else
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Shop') }}</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Status') }}</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Webhooks') }}</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Last Check') }}</th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach ($shops as $shop)
                                        <tr>
                                            <td class="px-6 py-4">
                                                <div class="text-sm font-medium text-gray-900">{{ $shop->name }}</div>
                                                <div class="text-sm text-gray-500 flex items-start gap-2">
                                                    <button
                                                        type="button"
                                                        class="text-left truncate max-w-xs hover:text-gray-700 cursor-pointer"
                                                        title="Click to expand"
                                                        onclick="this.classList.toggle('truncate'); this.classList.toggle('max-w-xs'); this.classList.toggle('whitespace-normal'); this.classList.toggle('break-all');"
                                                    >
                                                        {{ $shop->shop_url }}
                                                    </button>
                                                    <button
                                                        type="button"
                                                        class="text-gray-400 hover:text-indigo-600 flex-shrink-0"
                                                        title="Copy URL"
                                                        onclick="navigator.clipboard.writeText('{{ $shop->shop_url }}'); this.innerHTML = '<svg class=\'w-4 h-4 text-green-500\' fill=\'none\' stroke=\'currentColor\' viewBox=\'0 0 24 24\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M5 13l4 4L19 7\'></path></svg>'; setTimeout(() => { this.innerHTML = '<svg class=\'w-4 h-4\' fill=\'none\' stroke=\'currentColor\' viewBox=\'0 0 24 24\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z\'></path></svg>'; }, 2000);"
                                                    >
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                                                        </svg>
                                                    </button>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                @if (!$shop->getRawOriginal('webhook_url'))
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">{{ __('No Webhook') }}</span>
                                                @elseif ($shop->active)
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">{{ __('Active') }}</span>
                                                @else
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">{{ __('Inactive') }}</span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ $shop->webhook_logs_count }} {{ __('sent') }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ $shop->last_order_check ? $shop->last_order_check->diffForHumans() : __('Never') }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                <a href="{{ route('shops.show', $shop) }}" class="inline-flex items-center text-indigo-600 hover:text-indigo-900 mr-4" title="{{ __('View') }}">
                                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                                    </svg>
                                                    {{ __('View') }}
                                                </a>
                                                <a href="{{ route('shops.webhooks.index', $shop) }}" class="inline-flex items-center text-green-600 hover:text-green-900 mr-4" title="{{ __('Webhooks') }}">
                                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                                    </svg>
                                                    {{ __('Webhooks') }}
                                                </a>
                                                <a href="{{ route('shops.edit', $shop) }}" class="inline-flex items-center text-gray-600 hover:text-gray-900" title="{{ __('Settings') }}">
                                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                    </svg>
                                                    {{ __('Settings') }}
                                                </a>
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
