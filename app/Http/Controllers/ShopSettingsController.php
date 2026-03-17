<?php

namespace App\Http\Controllers;

use App\Models\Shop;
use App\Services\EpagesApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class ShopSettingsController extends Controller
{
    public function show(Shop $shop)
    {
        $this->authorizeShop($shop);

        $shop->loadCount('webhookLogs');
        $recentWebhooks = $shop->webhookLogs()->latest()->take(5)->get();

        return view('shops.show', [
            'shop' => $shop,
            'recentWebhooks' => $recentWebhooks,
        ]);
    }

    public function edit(Shop $shop)
    {
        $this->authorizeShop($shop);

        return view('shops.edit', [
            'shop' => $shop,
        ]);
    }

    public function update(Request $request, Shop $shop)
    {
        $this->authorizeShop($shop);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'webhook_url' => ['required', 'url'],
            'polling_interval_minutes' => ['required', 'integer', 'min:1', 'max:60'],
            'active' => ['boolean'],
        ]);

        $shop->update([
            'name' => $validated['name'],
            'webhook_url' => $validated['webhook_url'],
            'polling_interval_minutes' => $validated['polling_interval_minutes'],
            'active' => $request->boolean('active'),
        ]);

        return redirect()->route('shops.show', $shop)
            ->with('success', 'Shop settings updated successfully.');
    }

    public function reactivate(Shop $shop)
    {
        $this->authorizeShop($shop);

        // Test connection before reactivating
        $apiService = new EpagesApiService($shop);
        $result = $apiService->getOrdersWithResult(now());

        if (!$result->isSuccess()) {
            return redirect()->route('shops.show', $shop)
                ->with('error', 'Cannot reactivate: API connection failed. ' . $result->getFailureReason());
        }

        // Connection successful - reactivate the shop
        $shop->reactivate();

        return redirect()->route('shops.show', $shop)
            ->with('success', 'Shop reactivated successfully. API connection verified.');
    }

    protected function authorizeShop(Shop $shop): void
    {
        if ($shop->user_id !== Auth::id()) {
            abort(403, 'You do not have access to this shop.');
        }
    }
}
