<?php

namespace App\Jobs;

use App\Models\Shop;
use App\Services\EpagesApiService;
use App\Services\WebhookService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class PollShopOrders implements ShouldQueue
{
    use Queueable;

    protected Shop $shop;

    public function __construct(Shop $shop)
    {
        $this->shop = $shop;
    }

    public function handle(): void
    {
        if (!$this->shop->shouldPoll()) {
            return;
        }

        Log::info('Polling orders for shop', ['shop_id' => $this->shop->id, 'shop_name' => $this->shop->name]);

        $apiService = new EpagesApiService($this->shop);
        $webhookService = new WebhookService();

        $since = $this->shop->last_processed_order_date;
        $orders = $apiService->getOrders($since);

        $newOrdersCount = 0;
        $latestOrderDate = $since;

        foreach ($orders as $order) {
            $orderDate = $this->parseOrderDate($order);
            
            if ($since && $orderDate <= $since) {
                continue;
            }

            try {
                $webhookService->sendOrderWebhook($this->shop, $order);
                $newOrdersCount++;

                if (!$latestOrderDate || $orderDate > $latestOrderDate) {
                    $latestOrderDate = $orderDate;
                }

                Log::info('Processed new order', [
                    'shop_id' => $this->shop->id,
                    'order_id' => $order['orderId'] ?? $order['id'] ?? 'unknown',
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to process order webhook', [
                    'shop_id' => $this->shop->id,
                    'order_id' => $order['orderId'] ?? $order['id'] ?? 'unknown',
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $this->shop->update([
            'last_order_check' => now(),
            'last_processed_order_date' => $latestOrderDate,
        ]);

        Log::info('Completed polling for shop', [
            'shop_id' => $this->shop->id,
            'new_orders' => $newOrdersCount,
            'total_orders_fetched' => count($orders),
        ]);
    }

    protected function parseOrderDate(array $order): Carbon
    {
        $dateField = $order['creationDate'] ?? $order['created_at'] ?? $order['date'] ?? now()->toISOString();
        return Carbon::parse($dateField);
    }
}
