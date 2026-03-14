<?php

namespace EpagesIntegration\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use EpagesIntegration\Models\EpagesShop;

class OAuthService
{
    protected ?string $clientId;
    protected ?string $clientSecret;
    protected ?string $redirectUri;
    protected string $apiBaseUrl;

    public function __construct(
        ?string $clientId = null,
        ?string $clientSecret = null,
        ?string $redirectUri = null
    ) {
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
        $this->redirectUri = $redirectUri;
        $this->apiBaseUrl = config('epages.api_base_url', 'https://api.epages.com');
    }

    protected function ensureConfigured(): void
    {
        if (empty($this->clientId) || empty($this->clientSecret) || empty($this->redirectUri)) {
            throw new \RuntimeException(
                'ePages OAuth is not configured. Please set EPAGES_CLIENT_ID, EPAGES_CLIENT_SECRET, and EPAGES_REDIRECT_URI in your .env file.'
            );
        }
    }

    public function getAuthorizationUrl(string $shopUrl, ?string $state = null): string
    {
        $this->ensureConfigured();

        $state = $state ?? Str::random(40);

        session(['epages_oauth_state' => $state]);

        $params = http_build_query([
            'client_id' => $this->clientId,
            'redirect_uri' => $this->redirectUri,
            'response_type' => 'code',
            'scope' => config('epages.scopes', 'read_products'),
            'state' => $state,
        ]);

        return rtrim($shopUrl, '/') . '/oauth/authorize?' . $params;
    }

    public function exchangeCodeForToken(string $code, string $shopUrl): array
    {
        $this->ensureConfigured();

        $tokenUrl = rtrim($shopUrl, '/') . '/oauth/token';

        $response = Http::asForm()->post($tokenUrl, [
            'grant_type' => 'authorization_code',
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'code' => $code,
            'redirect_uri' => $this->redirectUri,
        ]);

        if (!$response->successful()) {
            throw new \Exception('Failed to exchange code for token: ' . $response->body());
        }

        return $response->json();
    }

    public function refreshToken(EpagesShop $shop): array
    {
        $this->ensureConfigured();

        $tokenUrl = rtrim($shop->shop_url, '/') . '/oauth/token';

        $response = Http::asForm()->post($tokenUrl, [
            'grant_type' => 'refresh_token',
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'refresh_token' => $shop->refresh_token,
        ]);

        if (!$response->successful()) {
            throw new \Exception('Failed to refresh token: ' . $response->body());
        }

        $tokenData = $response->json();

        $shop->update([
            'access_token' => $tokenData['access_token'],
            'refresh_token' => $tokenData['refresh_token'] ?? $shop->refresh_token,
            'token_expires_at' => now()->addSeconds($tokenData['expires_in'] ?? 3600),
        ]);

        return $tokenData;
    }

    public function storeCredentials(string $shopId, string $shopUrl, array $tokenData): EpagesShop
    {
        return EpagesShop::updateOrCreate(
            ['shop_id' => $shopId],
            [
                'shop_url' => $shopUrl,
                'access_token' => $tokenData['access_token'],
                'refresh_token' => $tokenData['refresh_token'] ?? null,
                'token_expires_at' => isset($tokenData['expires_in'])
                    ? now()->addSeconds($tokenData['expires_in'])
                    : null,
                'scopes' => $tokenData['scope'] ?? config('epages.scopes'),
                'is_active' => true,
                'installed_at' => now(),
            ]
        );
    }

    public function revokeToken(EpagesShop $shop): bool
    {
        $shop->update([
            'is_active' => false,
            'uninstalled_at' => now(),
        ]);

        return true;
    }

    public function isTokenExpired(EpagesShop $shop): bool
    {
        if (!$shop->token_expires_at) {
            return false;
        }

        return $shop->token_expires_at->isPast();
    }

    public function getValidToken(EpagesShop $shop): string
    {
        if ($this->isTokenExpired($shop)) {
            $this->refreshToken($shop);
            $shop->refresh();
        }

        return $shop->access_token;
    }
}
