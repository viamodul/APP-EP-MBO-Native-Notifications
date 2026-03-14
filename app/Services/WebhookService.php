<?php

namespace App\Services;

use App\Models\Shop;
use App\Models\WebhookLog;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class WebhookService
{
    public function sendOrderWebhook(Shop $shop, array $orderData): WebhookLog
    {
        $payload = $this->createWebhookPayload($shop, $orderData);
        $webhookUrl = $this->getWebhookUrl($shop);

        $webhookLog = WebhookLog::create([
            'shop_id' => $shop->id,
            'order_id' => $orderData['orderId'] ?? $orderData['id'] ?? '',
            'order_created_at' => $this->parseOrderDate($orderData),
            'order_data' => $orderData,
            'webhook_payload' => $payload,
            'webhook_url' => $webhookUrl,
            'status' => 'pending',
        ]);

        $this->deliverWebhook($webhookLog);

        return $webhookLog;
    }

    protected function getWebhookUrl(Shop $shop): string
    {
        // Shop-specific webhook URL takes priority, then fallback to global config
        return $shop->webhook_url ?? config('services.epages.webhook_url', env('WEBHOOK_URL'));
    }

    protected function createWebhookPayload(Shop $shop, array $orderData): array
    {
        return [
            'event' => 'order.created',
            'timestamp' => now()->toISOString(),
            'shop' => [
                'id' => $shop->id,
                'name' => $shop->name,
                'url' => $shop->shop_url,
                'version' => $shop->epages_version,
            ],
            'order' => [
                'id' => $orderData['orderId'] ?? $orderData['id'] ?? '',
                'created_at' => $this->parseOrderDate($orderData)->toISOString(),
                'total' => $orderData['grandTotal'] ?? $orderData['total'] ?? 0,
                'currency' => $orderData['currency'] ?? 'EUR',
                'customer' => [
                    'email' => $orderData['billingAddress']['emailAddress'] ?? '',
                    'name' => $this->getCustomerName($orderData),
                ],
                'items_count' => count($orderData['lineItems'] ?? []),
                'status' => $orderData['orderStatus'] ?? 'new',
            ],
            'raw_data' => $orderData,
        ];
    }

    protected function deliverWebhook(WebhookLog $webhookLog): void
    {
        $webhookUrl = $webhookLog->webhook_url;

        if (!$webhookUrl) {
            Log::warning('Webhook URL not configured', ['webhook_log_id' => $webhookLog->id]);
            $webhookLog->update([
                'status' => 'failed',
                'response_body' => 'Webhook URL not configured',
            ]);
            return;
        }

        try {
            $http = Http::timeout(30)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'X-Webhook-Source' => 'epages-webhook-simulator',
                    'X-Shop-Id' => (string) $webhookLog->shop_id,
                ]);

            // Disable SSL verification in local/development environments
            if (app()->environment('local', 'development')) {
                $http = $http->withOptions(['verify' => false]);
            }

            $response = $http->post($webhookUrl, $webhookLog->webhook_payload);

            $webhookLog->update([
                'response_status' => $response->status(),
                'response_body' => $response->body(),
                'status' => $response->successful() ? 'sent' : 'failed',
                'sent_at' => now(),
            ]);

            if ($response->successful()) {
                Log::info('Webhook delivered successfully', [
                    'webhook_log_id' => $webhookLog->id,
                    'shop_id' => $webhookLog->shop_id,
                    'order_id' => $webhookLog->order_id,
                ]);
            } else {
                Log::warning('Webhook delivery failed', [
                    'webhook_log_id' => $webhookLog->id,
                    'status' => $response->status(),
                    'response' => $response->body(),
                ]);
            }
        } catch (\Exception $e) {
            $webhookLog->update([
                'status' => 'failed',
                'response_body' => $e->getMessage(),
            ]);

            Log::error('Exception delivering webhook', [
                'webhook_log_id' => $webhookLog->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function retryFailedWebhook(WebhookLog $webhookLog): void
    {
        if ($webhookLog->retry_count >= 3) {
            Log::warning('Max retry attempts reached for webhook', [
                'webhook_log_id' => $webhookLog->id,
            ]);
            return;
        }

        $webhookLog->increment('retry_count');
        $webhookLog->update(['status' => 'pending']);
        
        $this->deliverWebhook($webhookLog);
    }

    protected function parseOrderDate(array $orderData): Carbon
    {
        $dateField = $orderData['creationDate'] ?? $orderData['created_at'] ?? $orderData['date'] ?? now()->toISOString();
        return Carbon::parse($dateField);
    }

    protected function getCustomerName(array $orderData): string
    {
        $billing = $orderData['billingAddress'] ?? [];
        $firstName = $billing['firstName'] ?? '';
        $lastName = $billing['lastName'] ?? '';
        
        return trim($firstName . ' ' . $lastName) ?: 'Unknown Customer';
    }
}