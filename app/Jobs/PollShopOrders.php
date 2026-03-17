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

    /**
     * Maximum consecutive API failures before deactivating shop.
     */
    protected int $maxApiFailures = 3;

    public function __construct(Shop $shop)
    {
        $this->shop = $shop;
    }

    public function handle(): void
    {
        // Refresh shop to get latest state
        $this->shop->refresh();

        if (!$this->shop->shouldPoll()) {
            return;
        }

        Log::info('Polling orders for shop', ['shop_id' => $this->shop->id, 'shop_name' => $this->shop->name]);

        $apiService = new EpagesApiService($this->shop);
        $webhookService = new WebhookService();

        $since = $this->shop->last_processed_order_date;

        // On first run, skip historical orders if configured (useful for production)
        if (is_null($since) && config('services.epages.skip_historical_orders', false)) {
            $since = now();
            Log::info('First poll: skipping historical orders, starting from now', ['shop_id' => $this->shop->id]);
        }

        // Get orders with structured result for proper error handling
        $result = $apiService->getOrdersWithResult($since);

        // Handle API failures - shop may be unavailable or deleted
        if (!$result->isSuccess()) {
            $this->handleApiFailure($result);
            return;
        }

        // API call was successful - reset failure counter
        $this->shop->recordApiSuccess();

        $orders = $result->data;
        $newOrdersCount = 0;
        $latestOrderDate = $since;
        $limitReached = false;

        foreach ($orders as $order) {
            $orderDate = $this->parseOrderDate($order);

            if ($since && $orderDate <= $since) {
                continue;
            }

            try {
                $webhookLog = $webhookService->sendOrderWebhook($this->shop, $order);

                // Check if webhook was blocked due to subscription limits
                if ($webhookLog === null) {
                    Log::warning('Webhook limit reached, stopping order processing', [
                        'shop_id' => $this->shop->id,
                        'user_id' => $this->shop->user_id,
                    ]);
                    $limitReached = true;
                    break;
                }

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
            'limit_reached' => $limitReached,
        ]);
    }

    /**
     * Handle API failure and potentially deactivate shop.
     */
    protected function handleApiFailure(\App\Services\ApiResult $result): void
    {
        $reason = $result->getFailureReason();
        $failureCount = $this->shop->api_failure_count + 1;

        Log::warning('API failure for shop', [
            'shop_id' => $this->shop->id,
            'shop_name' => $this->shop->name,
            'failure_reason' => $reason,
            'failure_count' => $failureCount,
            'max_failures' => $this->maxApiFailures,
        ]);

        // Only count shop unavailability errors towards deactivation
        // Server errors (500) might be temporary, so we still count them but log differently
        if ($result->isShopUnavailable()) {
            $wasDeactivated = $this->shop->recordApiFailure($reason, $this->maxApiFailures);

            if ($wasDeactivated) {
                Log::error('Shop auto-deactivated due to consecutive API failures', [
                    'shop_id' => $this->shop->id,
                    'shop_name' => $this->shop->name,
                    'shop_url' => $this->shop->shop_url,
                    'final_reason' => $reason,
                    'total_failures' => $this->maxApiFailures,
                ]);
            }
        } else {
            // For other errors (like 500), just log but don't count towards deactivation
            Log::error('Temporary API error for shop', [
                'shop_id' => $this->shop->id,
                'reason' => $reason,
            ]);
        }

        // Update last check time even on failure to prevent immediate retry
        $this->shop->update(['last_order_check' => now()]);
    }

    protected function parseOrderDate(array $order): Carbon
    {
        $dateField = $order['creationDate'] ?? $order['created_at'] ?? $order['date'] ?? now()->toISOString();
        return Carbon::parse($dateField);
    }
}
