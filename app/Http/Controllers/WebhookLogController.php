<?php

namespace App\Http\Controllers;

use App\Models\Shop;
use App\Models\WebhookLog;
use App\Services\WebhookService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WebhookLogController extends Controller
{
    public function index(Request $request, Shop $shop)
    {
        $this->authorizeShop($shop);

        $webhooks = $shop->webhookLogs()
            ->when($request->status, fn($q, $status) => $q->where('status', $status))
            ->when($request->search, function ($q, $search) {
                $q->where(function ($query) use ($search) {
                    $query->where('order_id', 'like', "%{$search}%")
                          ->orWhereRaw("JSON_UNQUOTE(JSON_EXTRACT(order_data, '$.orderNumber')) LIKE ?", ["%{$search}%"]);
                });
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('shops.webhooks.index', [
            'shop' => $shop,
            'webhooks' => $webhooks,
            'currentStatus' => $request->status,
            'search' => $request->search,
        ]);
    }

    public function show(Shop $shop, WebhookLog $webhookLog)
    {
        $this->authorizeShop($shop);

        if ($webhookLog->shop_id !== $shop->id) {
            abort(404);
        }

        return view('shops.webhooks.show', [
            'shop' => $shop,
            'webhook' => $webhookLog,
        ]);
    }

    public function retry(Shop $shop, WebhookLog $webhookLog, WebhookService $webhookService)
    {
        $this->authorizeShop($shop);

        if ($webhookLog->shop_id !== $shop->id) {
            abort(404);
        }

        if ($webhookLog->status === 'sent') {
            return back()->with('error', 'This webhook was already sent successfully.');
        }

        if ($webhookLog->retry_count >= 3) {
            return back()->with('error', 'Maximum retry attempts reached.');
        }

        $webhookService->retryFailedWebhook($webhookLog);

        return back()->with('success', 'Webhook retry initiated.');
    }

    protected function authorizeShop(Shop $shop): void
    {
        if ($shop->user_id !== Auth::id()) {
            abort(403, 'You do not have access to this shop.');
        }
    }
}
