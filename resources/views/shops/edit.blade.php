<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Edit Shop: {{ $shop->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg">
                <div class="p-6">
                    @if ($errors->any())
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                            <ul class="list-disc list-inside text-sm">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('shops.update', $shop) }}" method="POST">
                        @csrf
                        @method('PATCH')

                        <div class="mb-4">
                            <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Shop Name</label>
                            <input
                                type="text"
                                id="name"
                                name="name"
                                value="{{ old('name', $shop->name) }}"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                required
                            >
                        </div>

                        <div class="mb-4">
                            <label for="webhook_url" class="block text-sm font-medium text-gray-700 mb-1">Webhook URL</label>
                            <input
                                type="url"
                                id="webhook_url"
                                name="webhook_url"
                                value="{{ old('webhook_url', $shop->getRawOriginal('webhook_url')) }}"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                placeholder="https://your-server.com/webhook"
                                required
                            >
                            <p class="text-sm text-gray-500 mt-1">POST requests will be sent to this URL for each new order.</p>
                        </div>

                        <div class="mb-4">
                            <label for="polling_interval_minutes" class="block text-sm font-medium text-gray-700 mb-1">Polling Interval (minutes)</label>
                            <input
                                type="number"
                                id="polling_interval_minutes"
                                name="polling_interval_minutes"
                                value="{{ old('polling_interval_minutes', $shop->polling_interval_minutes) }}"
                                min="1"
                                max="60"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                required
                            >
                            <p class="text-sm text-gray-500 mt-1">How often to check for new orders (1-60 minutes).</p>
                        </div>

                        <div class="mb-6">
                            <label class="flex items-center">
                                <input
                                    type="checkbox"
                                    name="active"
                                    value="1"
                                    {{ old('active', $shop->active) ? 'checked' : '' }}
                                    class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                                >
                                <span class="ml-2 text-sm text-gray-700">Active (enable polling for this shop)</span>
                            </label>
                        </div>

                        <div class="flex justify-between items-center">
                            <a href="{{ route('shops.show', $shop) }}" class="text-gray-600 hover:text-gray-900">Cancel</a>
                            <button
                                type="submit"
                                class="bg-indigo-600 text-white py-2 px-4 rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                            >
                                Save Changes
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
