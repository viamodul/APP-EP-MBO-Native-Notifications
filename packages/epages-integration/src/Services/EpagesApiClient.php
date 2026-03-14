<?php

namespace EpagesIntegration\Services;

use EpagesIntegration\Models\EpagesShop;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\Response;

class EpagesApiClient
{
    protected EpagesShop $shop;
    protected int $timeout;

    public function __construct(EpagesShop $shop)
    {
        $this->shop = $shop;
        $this->timeout = config('epages.timeout', 30);
    }

    public static function for(EpagesShop $shop): self
    {
        return new self($shop);
    }

    protected function request(): \Illuminate\Http\Client\PendingRequest
    {
        return Http::withToken($this->shop->access_token)
            ->timeout($this->timeout)
            ->acceptJson();
    }

    protected function apiUrl(string $endpoint = ''): string
    {
        return rtrim($this->shop->shop_url, '/') . '/' . ltrim($endpoint, '/');
    }

    public function get(string $endpoint): Response
    {
        return $this->request()->get($this->apiUrl($endpoint));
    }

    public function post(string $endpoint, array $data = []): Response
    {
        return $this->request()->post($this->apiUrl($endpoint), $data);
    }

    public function put(string $endpoint, array $data = []): Response
    {
        return $this->request()->put($this->apiUrl($endpoint), $data);
    }

    public function delete(string $endpoint): Response
    {
        return $this->request()->delete($this->apiUrl($endpoint));
    }

    public function verifyConnection(): array
    {
        try {
            $response = $this->get('');

            if ($response->successful()) {
                return [
                    'connected' => true,
                    'status' => 'active',
                    'message' => 'Connection successful',
                    'shop_info' => $response->json(),
                ];
            }

            if ($response->status() === 401) {
                return [
                    'connected' => false,
                    'status' => 'unauthorized',
                    'message' => 'Access token is invalid or expired',
                ];
            }

            return [
                'connected' => false,
                'status' => 'error',
                'message' => 'API returned status: ' . $response->status(),
            ];
        } catch (\Exception $e) {
            return [
                'connected' => false,
                'status' => 'error',
                'message' => 'Connection failed: ' . $e->getMessage(),
            ];
        }
    }
}
