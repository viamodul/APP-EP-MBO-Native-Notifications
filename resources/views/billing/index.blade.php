<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Billing & Plans') }}
        </h2>
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

            @if (request('checkout') === 'success')
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                    {{ __('Your subscription has been activated! Welcome aboard.') }}
                </div>
            @endif

            @if (request('checkout') === 'cancelled')
                <div class="mb-4 bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded">
                    {{ __("Checkout was cancelled. You can try again when you're ready.") }}
                </div>
            @endif

            {{-- Current Plan & Usage --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">{{ __('Current Plan') }}</h3>

                    <div class="flex flex-col md:flex-row md:items-center md:justify-between">
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="text-2xl font-bold text-gray-900">{{ $tierConfig['name'] }}</span>
                                @if ($isOnTrial)
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                                        {{ __('Trial - :days days left', ['days' => $daysUntilTrialExpires]) }}
                                    </span>
                                @elseif ($trialExpired)
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">
                                        {{ __('Trial Expired') }}
                                    </span>
                                @elseif ($user->subscription('default')?->onGracePeriod())
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                        {{ __('Cancels at period end') }}
                                    </span>
                                @endif
                            </div>

                            <ul class="mt-2 text-sm text-gray-600 space-y-1">
                                <li>
                                    <span class="font-medium">{{ __('Shops:') }}</span>
                                    {{ $tierConfig['shops_limit'] ?? __('Unlimited') }}
                                </li>
                                <li>
                                    <span class="font-medium">{{ __('Webhooks/month:') }}</span>
                                    {{ $tierConfig['webhooks_limit'] ? number_format($tierConfig['webhooks_limit']) : __('Unlimited') }}
                                </li>
                                <li>
                                    <span class="font-medium">{{ __('Log retention:') }}</span>
                                    {{ __(':days days', ['days' => $tierConfig['log_retention_days']]) }}
                                </li>
                                <li>
                                    <span class="font-medium">{{ __('Polling interval:') }}</span>
                                    {{ __(':n minutes', ['n' => $tierConfig['polling_interval_minutes']]) }}
                                </li>
                            </ul>
                        </div>

                        @if ($user->isOnPaidTier() && $user->subscription('default'))
                            <div class="mt-4 md:mt-0">
                                <a href="{{ route('billing.portal') }}"
                                   class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                                    {{ __('Manage Subscription') }}
                                </a>
                            </div>
                        @endif
                    </div>

                    {{-- Usage Progress --}}
                    @if ($webhooksLimit !== null)
                        <div class="mt-6">
                            <div class="flex justify-between text-sm text-gray-600 mb-1">
                                <span>{{ __('Webhooks used this period') }}</span>
                                <span>{{ number_format($webhooksSent) }} / {{ number_format($webhooksLimit) }}</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2.5">
                                @php
                                    $percentage = min(100, $usagePercentage ?? 0);
                                    $barColor = match(true) {
                                        $percentage >= 100 => 'bg-red-600',
                                        $percentage >= 90 => 'bg-orange-500',
                                        $percentage >= 75 => 'bg-yellow-500',
                                        default => 'bg-green-600',
                                    };
                                @endphp
                                <div class="{{ $barColor }} h-2.5 rounded-full transition-all duration-300"
                                     style="width: {{ $percentage }}%"></div>
                            </div>
                            @if ($usagePercentage >= 100)
                                <p class="mt-2 text-sm text-red-600">
                                    {{ __("You've reached your webhook limit. Upgrade your plan to continue receiving notifications.") }}
                                </p>
                            @elseif ($remainingWebhooks !== null)
                                <p class="mt-2 text-sm text-gray-500">
                                    {{ __(':count webhooks remaining', ['count' => number_format($remainingWebhooks)]) }}
                                </p>
                            @endif
                        </div>
                    @else
                        <div class="mt-6">
                            <div class="flex items-center text-sm text-gray-600">
                                <svg class="w-5 h-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                <span>{{ __('Unlimited webhooks - :count sent this period', ['count' => number_format($webhooksSent)]) }}</span>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Subscription Plans --}}
            @if ($isOnTrial || $trialExpired || !$user->isOnPaidTier())
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-6">{{ __('Choose a Plan') }}</h3>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                            @foreach ($tiers as $tierKey => $tier)
                                <div class="border rounded-lg p-6 {{ $tierKey === 'pro' ? 'border-indigo-500 ring-2 ring-indigo-500' : 'border-gray-200' }}">
                                    @if ($tierKey === 'pro')
                                        <div class="text-center mb-2">
                                            <span class="px-3 py-1 text-xs font-semibold rounded-full bg-indigo-100 text-indigo-800">
                                                {{ __('Most Popular') }}
                                            </span>
                                        </div>
                                    @endif

                                    <h4 class="text-xl font-bold text-gray-900 text-center">{{ $tier['name'] }}</h4>

                                    <div class="mt-4 text-center">
                                        <span class="text-4xl font-bold text-gray-900">EUR {{ number_format($tier['prices']['monthly'] / 100) }}</span>
                                        <span class="text-gray-500">/{{ __('month') }}</span>
                                    </div>

                                    <p class="mt-1 text-center text-sm text-gray-500">
                                        {{ __('or EUR :price/year', ['price' => number_format($tier['prices']['yearly'] / 100)]) }}
                                        <span class="text-green-600">({{ __('save :percent%', ['percent' => round((1 - ($tier['prices']['yearly'] / ($tier['prices']['monthly'] * 12))) * 100)]) }})</span>
                                    </p>

                                    <ul class="mt-6 space-y-3">
                                        <li class="flex items-center text-sm text-gray-600">
                                            <svg class="w-5 h-5 text-green-500 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                            </svg>
                                            {{ ($tier['shops_limit'] ?? __('Unlimited')) . ' ' . Str::plural(__('shop'), $tier['shops_limit'] ?? 2) }}
                                        </li>
                                        <li class="flex items-center text-sm text-gray-600">
                                            <svg class="w-5 h-5 text-green-500 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                            </svg>
                                            {{ ($tier['webhooks_limit'] ? number_format($tier['webhooks_limit']) : __('Unlimited')) . ' ' . __('webhooks/month') }}
                                        </li>
                                        <li class="flex items-center text-sm text-gray-600">
                                            <svg class="w-5 h-5 text-green-500 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                            </svg>
                                            {{ __(':days-day log retention', ['days' => $tier['log_retention_days']]) }}
                                        </li>
                                        <li class="flex items-center text-sm text-gray-600">
                                            <svg class="w-5 h-5 text-green-500 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                            </svg>
                                            {{ __(':n-minute polling', ['n' => $tier['polling_interval_minutes']]) }}
                                        </li>
                                    </ul>

                                    <div class="mt-6 space-y-2">
                                        <form action="{{ route('billing.checkout') }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="tier" value="{{ $tierKey }}">
                                            <input type="hidden" name="interval" value="monthly">
                                            <button type="submit"
                                                    class="w-full px-4 py-2 {{ $tierKey === 'pro' ? 'bg-indigo-600 hover:bg-indigo-700' : 'bg-gray-800 hover:bg-gray-700' }} border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest">
                                                {{ __('Subscribe Monthly') }}
                                            </button>
                                        </form>
                                        <form action="{{ route('billing.checkout') }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="tier" value="{{ $tierKey }}">
                                            <input type="hidden" name="interval" value="yearly">
                                            <button type="submit"
                                                    class="w-full px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-50">
                                                {{ __('Subscribe Yearly') }}
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

            {{-- Manage Existing Subscription --}}
            @if ($user->subscription('default'))
                <div class="mt-6 bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">{{ __('Subscription Management') }}</h3>

                        <div class="flex flex-wrap gap-4">
                            <a href="{{ route('billing.portal') }}"
                               class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                                {{ __('Update Payment Method') }}
                            </a>

                            <a href="{{ route('billing.portal') }}"
                               class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-50">
                                {{ __('View Invoices') }}
                            </a>

                            @if ($user->subscription('default')->active() && !$user->subscription('default')->onGracePeriod())
                                <form action="{{ route('billing.cancel') }}" method="POST"
                                      onsubmit="return confirm('{{ __('Are you sure you want to cancel your subscription?') }}');">
                                    @csrf
                                    <button type="submit"
                                            class="inline-flex items-center px-4 py-2 bg-white border border-red-300 rounded-md font-semibold text-xs text-red-700 uppercase tracking-widest hover:bg-red-50">
                                        {{ __('Cancel Subscription') }}
                                    </button>
                                </form>
                            @elseif ($user->subscription('default')->onGracePeriod())
                                <form action="{{ route('billing.resume') }}" method="POST">
                                    @csrf
                                    <button type="submit"
                                            class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700">
                                        {{ __('Resume Subscription') }}
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
