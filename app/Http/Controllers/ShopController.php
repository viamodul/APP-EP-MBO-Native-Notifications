<?php

namespace App\Http\Controllers;

use App\Jobs\PollShopOrders;
use App\Models\Shop;
use App\Services\EpagesApiService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ShopController extends Controller
{
    public function index(): JsonResponse
    {
        $shops = Shop::with('webhookLogs')->get();
        return response()->json($shops);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'shop_url' => 'required|url',
            'epages_version' => 'required|in:now,base',
            'api_token' => 'required|string',
            'polling_interval_minutes' => 'integer|min:1|max:1440',
            'group_name' => 'nullable|string|max:255',
            'active' => 'boolean',
        ]);

        $shop = Shop::create($validated);
        
        return response()->json($shop, 201);
    }

    public function show(Shop $shop): JsonResponse
    {
        $shop->load('webhookLogs');
        return response()->json($shop);
    }

    public function update(Request $request, Shop $shop): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'string|max:255',
            'shop_url' => 'url',
            'epages_version' => 'in:now,base',
            'api_token' => 'string',
            'polling_interval_minutes' => 'integer|min:1|max:1440',
            'group_name' => 'nullable|string|max:255',
            'active' => 'boolean',
        ]);

        $shop->update($validated);
        
        return response()->json($shop);
    }

    public function destroy(Shop $shop): JsonResponse
    {
        $shop->delete();
        return response()->json(['message' => 'Shop deleted successfully']);
    }

    public function testConnection(Shop $shop): JsonResponse
    {
        $apiService = new EpagesApiService($shop);
        $isConnected = $apiService->testConnection();
        
        return response()->json([
            'shop_id' => $shop->id,
            'connected' => $isConnected,
            'message' => $isConnected ? 'Connection successful' : 'Connection failed',
        ]);
    }

    public function pollNow(Shop $shop): JsonResponse
    {
        if (!$shop->active) {
            return response()->json([
                'message' => 'Shop is not active',
            ], 400);
        }

        PollShopOrders::dispatch($shop);
        
        return response()->json([
            'message' => 'Polling job dispatched for shop',
            'shop_id' => $shop->id,
        ]);
    }
}
