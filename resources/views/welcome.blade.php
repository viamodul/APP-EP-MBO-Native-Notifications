<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'ePages Webhooks') }} - Real-time Order Notifications</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="/favicon-96x96.png" sizes="96x96" />
    <link rel="icon" type="image/svg+xml" href="/favicon.svg" />
    <link rel="shortcut icon" href="/favicon.ico" />
    <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png" />
    <meta name="apple-mobile-web-app-title" content="ePages Webhooks" />
    <link rel="manifest" href="/site.webmanifest" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-gray-50">
    <!-- Navigation -->
    <nav class="bg-white shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <img src="{{ asset('images/logo.png') }}" alt="{{ config('app.name') }}" class="h-10 w-auto" />
                </div>
                <div class="flex items-center space-x-4">
                    @auth
                        <a href="{{ route('dashboard') }}" class="text-gray-700 hover:text-indigo-600 font-medium">
                            Dashboard
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="text-gray-700 hover:text-indigo-600 font-medium">
                            Login
                        </a>
                    @endauth
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="bg-gradient-to-br from-indigo-600 to-purple-700 text-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20 lg:py-32">
            <div class="text-center max-w-3xl mx-auto">
                <h1 class="text-4xl lg:text-5xl font-bold mb-6">
                    Real-time Order Notifications for ePages
                </h1>
                <p class="text-xl lg:text-2xl text-indigo-100 mb-8">
                    Receive instant webhook notifications whenever a new order is placed in your ePages shop.
                    Integrate with your systems, automate workflows, and never miss a sale.
                </p>
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <a href="#plans" class="inline-flex items-center justify-center px-6 py-3 bg-white text-indigo-600 font-semibold rounded-lg hover:bg-indigo-50 transition">
                        View Plans
                    </a>
                    <a href="#how-it-works" class="inline-flex items-center justify-center px-6 py-3 border-2 border-white text-white font-semibold rounded-lg hover:bg-white/10 transition">
                        How it Works
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="py-16 lg:py-24 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h2 class="text-3xl font-bold text-gray-900 mb-4">Why Use Webhook Notifications?</h2>
                <p class="text-lg text-gray-600 max-w-2xl mx-auto">
                    Stop polling for orders manually. Get instant notifications the moment a customer places an order.
                </p>
            </div>

            <div class="grid md:grid-cols-3 gap-8">
                <div class="text-center p-6">
                    <div class="w-14 h-14 bg-indigo-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-7 h-7 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-2">Instant Delivery</h3>
                    <p class="text-gray-600">
                        Webhooks are sent within seconds of a new order, enabling real-time integrations.
                    </p>
                </div>

                <div class="text-center p-6">
                    <div class="w-14 h-14 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-7 h-7 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-2">Reliable & Logged</h3>
                    <p class="text-gray-600">
                        Every webhook is logged with full details. Retry failed deliveries with one click.
                    </p>
                </div>

                <div class="text-center p-6">
                    <div class="w-14 h-14 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-7 h-7 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 4a2 2 0 114 0v1a1 1 0 001 1h3a1 1 0 011 1v3a1 1 0 01-1 1h-1a2 2 0 100 4h1a1 1 0 011 1v3a1 1 0 01-1 1h-3a1 1 0 01-1-1v-1a2 2 0 10-4 0v1a1 1 0 01-1 1H7a1 1 0 01-1-1v-3a1 1 0 00-1-1H4a2 2 0 110-4h1a1 1 0 001-1V7a1 1 0 011-1h3a1 1 0 001-1V4z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-2">Easy Integration</h3>
                    <p class="text-gray-600">
                        Standard JSON webhooks work with any system: Zapier, Make, custom backends, and more.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- How it Works Section -->
    <section id="how-it-works" class="py-16 lg:py-24 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h2 class="text-3xl font-bold text-gray-900 mb-4">How to Get Started</h2>
                <p class="text-lg text-gray-600 max-w-2xl mx-auto">
                    Three simple steps to start receiving order notifications.
                </p>
            </div>

            <div class="grid md:grid-cols-3 gap-8">
                <div class="bg-white rounded-xl p-8 shadow-sm border border-gray-100">
                    <div class="w-10 h-10 bg-indigo-600 text-white rounded-full flex items-center justify-center font-bold text-lg mb-4">
                        1
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-2">Install from ePages App Store</h3>
                    <p class="text-gray-600">
                        Find our app in the ePages App Store and install it on your shop. You'll be guided to create an account.
                    </p>
                </div>

                <div class="bg-white rounded-xl p-8 shadow-sm border border-gray-100">
                    <div class="w-10 h-10 bg-indigo-600 text-white rounded-full flex items-center justify-center font-bold text-lg mb-4">
                        2
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-2">Configure Your Webhook URL</h3>
                    <p class="text-gray-600">
                        Enter the URL where you want to receive notifications. This can be your backend, Zapier, or any HTTP endpoint.
                    </p>
                </div>

                <div class="bg-white rounded-xl p-8 shadow-sm border border-gray-100">
                    <div class="w-10 h-10 bg-indigo-600 text-white rounded-full flex items-center justify-center font-bold text-lg mb-4">
                        3
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-2">Receive Orders Instantly</h3>
                    <p class="text-gray-600">
                        That's it! Every new order will trigger a webhook to your endpoint with full order details.
                    </p>
                </div>
            </div>

            <div class="mt-12 text-center">
                <div class="inline-flex items-center bg-amber-50 border border-amber-200 rounded-lg px-6 py-4">
                    <svg class="w-6 h-6 text-amber-500 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <p class="text-amber-800">
                        <strong>Requirement:</strong> This app requires an ePages shop. Install it directly from the
                        <a href="https://www.epages.com/en/app-store/" target="_blank" class="underline hover:no-underline">ePages App Store</a>.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Pricing Section -->
    <section id="plans" class="py-16 lg:py-24 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h2 class="text-3xl font-bold text-gray-900 mb-4">Simple, Transparent Pricing</h2>
                <p class="text-lg text-gray-600 max-w-2xl mx-auto">
                    Start with a 14-day free trial. No credit card required.
                </p>
            </div>

            @php
                $tiers = collect(config('subscription.tiers'))
                    ->filter(fn($tier) => ($tier['visible'] ?? true) && isset($tier['prices']))
                    ->except('trial');
            @endphp

            <div class="grid sm:grid-cols-3 gap-8 max-w-5xl mx-auto">
                @foreach ($tiers as $tierKey => $tier)
                    <div class="bg-white rounded-xl p-8 {{ $tierKey === 'pro' ? 'ring-2 ring-indigo-600 shadow-lg scale-105' : 'border border-gray-200 shadow-sm' }}">
                        @if ($tierKey === 'pro')
                            <div class="text-center mb-4">
                                <span class="px-3 py-1 text-xs font-semibold rounded-full bg-indigo-100 text-indigo-800">
                                    Most Popular
                                </span>
                            </div>
                        @endif

                        <h3 class="text-2xl font-bold text-gray-900 text-center">{{ $tier['name'] }}</h3>

                        <div class="mt-4 text-center">
                            <span class="text-4xl font-bold text-gray-900">&euro;{{ number_format($tier['prices']['monthly'] / 100) }}</span>
                            <span class="text-gray-500">/month</span>
                        </div>

                        <p class="mt-2 text-center text-sm text-gray-500">
                            or &euro;{{ number_format($tier['prices']['yearly'] / 100) }}/year
                            <span class="text-green-600">(save {{ round((1 - ($tier['prices']['yearly'] / ($tier['prices']['monthly'] * 12))) * 100) }}%)</span>
                        </p>

                        <ul class="mt-8 space-y-4">
                            <li class="flex items-center text-gray-600">
                                <svg class="w-5 h-5 text-green-500 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                {{ $tier['shops_limit'] ?? 'Unlimited' }} {{ Str::plural('shop', $tier['shops_limit'] ?? 2) }}
                            </li>
                            <li class="flex items-center text-gray-600">
                                <svg class="w-5 h-5 text-green-500 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                {{ $tier['webhooks_limit'] ? number_format($tier['webhooks_limit']) : 'Unlimited' }} webhooks/month
                            </li>
                            <li class="flex items-center text-gray-600">
                                <svg class="w-5 h-5 text-green-500 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                {{ $tier['log_retention_days'] }}-day log retention
                            </li>
                            <li class="flex items-center text-gray-600">
                                <svg class="w-5 h-5 text-green-500 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                {{ $tier['polling_interval_minutes'] }}-minute polling
                            </li>
                        </ul>
                    </div>
                @endforeach
            </div>

            <div class="mt-12 text-center">
                <p class="text-gray-600">
                    All plans include a <strong>14-day free trial</strong>.
                    Install the app from ePages to get started.
                </p>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="py-16 lg:py-24 bg-indigo-600">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-3xl font-bold text-white mb-4">
                Ready to Automate Your Order Notifications?
            </h2>
            <p class="text-xl text-indigo-100 mb-8">
                Install our app from the ePages App Store and start your free trial today.
            </p>
            <a href="https://www.epages.com/en/app-store/" target="_blank"
               class="inline-flex items-center px-8 py-4 bg-white text-indigo-600 font-semibold rounded-lg hover:bg-indigo-50 transition text-lg">
                Visit ePages App Store
                <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                </svg>
            </a>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-gray-900 text-gray-400 py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col md:flex-row justify-between items-center">
                <div class="mb-4 md:mb-0">
                    <img src="{{ asset('images/logo.png') }}" alt="{{ config('app.name') }}" class="h-8 w-auto brightness-0 invert opacity-70" />
                </div>
                <div class="flex space-x-6">
                    @auth
                        <a href="{{ route('dashboard') }}" class="hover:text-white transition">Dashboard</a>
                    @else
                        <a href="{{ route('login') }}" class="hover:text-white transition">Login</a>
                    @endauth
                    <a href="#plans" class="hover:text-white transition">Pricing</a>
                </div>
            </div>
            <div class="mt-8 pt-8 border-t border-gray-800 text-center text-sm">
                <p>&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
            </div>
        </div>
    </footer>
</body>
</html>
