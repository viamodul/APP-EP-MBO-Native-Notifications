<?php

namespace App\Services;

use App\Models\Shop;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class EpagesApiService
{
    protected Shop $shop;
    protected string $baseUrl;

    public function __construct(Shop $shop)
    {
        $this->shop = $shop;
        $this->baseUrl = rtrim($shop->shop_url, '/');
    }

    /**
     * Get orders with structured result.
     */
    public function getOrdersWithResult(?Carbon $since = null): ApiResult
    {
        $endpoint = '/orders';
        $params = [
            'sort' => 'creationDate:desc',
            'size' => 100,
        ];

        if ($since) {
            $params['creationDateFrom'] = $since->toISOString();
        }

        try {
            $response = $this->makeRequest('GET', $endpoint, $params);

            if ($response->successful()) {
                $data = $response->json();
                return ApiResult::success($data['items'] ?? []);
            }

            Log::error('Failed to fetch orders from ePages', [
                'shop_id' => $this->shop->id,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return ApiResult::fromHttpStatus($response->status(), $response->body());
        } catch (ConnectionException $e) {
            Log::error('Connection error fetching orders from ePages', [
                'shop_id' => $this->shop->id,
                'error' => $e->getMessage(),
            ]);

            if (str_contains($e->getMessage(), 'timed out') || str_contains($e->getMessage(), 'timeout')) {
                return ApiResult::timeout();
            }

            return ApiResult::connectionError($e->getMessage());
        } catch (\Exception $e) {
            Log::error('Exception fetching orders from ePages', [
                'shop_id' => $this->shop->id,
                'error' => $e->getMessage(),
            ]);

            return new ApiResult(ApiResult::STATUS_UNKNOWN_ERROR, message: $e->getMessage());
        }
    }

    /**
     * @deprecated Use getOrdersWithResult() for proper error handling
     */
    public function getOrders(?Carbon $since = null): array
    {
        $result = $this->getOrdersWithResult($since);
        return $result->isSuccess() ? $result->data : [];
    }

    public function getOrder(string $orderId): ?array
    {
        $endpoint = "/orders/{$orderId}";

        try {
            $response = $this->makeRequest('GET', $endpoint);
            
            if ($response->successful()) {
                return $response->json();
            }

            Log::error('Failed to fetch order from ePages', [
                'shop_id' => $this->shop->id,
                'order_id' => $orderId,
                'status' => $response->status(),
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('Exception fetching order from ePages', [
                'shop_id' => $this->shop->id,
                'order_id' => $orderId,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    protected function makeRequest(string $method, string $endpoint, array $params = []): Response
    {
        $url = $this->baseUrl . $endpoint;
        
        $request = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->shop->api_token,
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ])->timeout(30);

        if ($method === 'GET' && !empty($params)) {
            return $request->get($url, $params);
        }

        return $request->send($method, $url, $params);
    }

    public function testConnection(): bool
    {
        try {
            $response = $this->makeRequest('GET', '/orders', ['size' => 1]);
            return $response->successful();
        } catch (\Exception $e) {
            Log::error('Failed to test ePages connection', [
                'shop_id' => $this->shop->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }
}