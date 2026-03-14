<?php

namespace EpagesIntegration\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use EpagesIntegration\Models\EpagesShop;

class InstallController extends Controller
{
    public function register(Request $request)
    {
        Log::info('ePages installation request received', [
            'params' => $request->all(),
        ]);

        // Validate required parameters
        $code = $request->input('code');
        $apiUrl = $request->input('api_url');
        $returnUrl = $request->input('return_url');
        $accessTokenUrl = $request->input('access_token_url');
        $signature = $request->input('signature');

        if (!$code || !$apiUrl || !$accessTokenUrl) {
            Log::error('ePages installation missing parameters', [
                'code' => $code ? 'present' : 'missing',
                'api_url' => $apiUrl ? 'present' : 'missing',
                'access_token_url' => $accessTokenUrl ? 'present' : 'missing',
            ]);

            return response()->json([
                'error' => 'Missing required parameters',
            ], 400);
        }

        // Verify signature if configured
        if (config('epages.verify_signatures', true) && $signature) {
            if (!$this->verifySignature($code, $accessTokenUrl, $signature)) {
                Log::warning('ePages installation signature verification failed', [
                    'api_url' => $apiUrl,
                ]);

                return response()->json([
                    'error' => 'Invalid signature',
                ], 403);
            }
        }

        // Exchange code for access token
        try {
            $tokenData = $this->exchangeCodeForToken($code, $accessTokenUrl);
        } catch (\Exception $e) {
            Log::error('ePages token exchange failed', [
                'error' => $e->getMessage(),
                'api_url' => $apiUrl,
            ]);

            return redirect($returnUrl ?? '/')->withErrors([
                'installation' => 'Failed to complete installation: ' . $e->getMessage(),
            ]);
        }

        // Store shop credentials
        $shopId = $this->extractShopId($apiUrl);

        $epagesShop = EpagesShop::updateOrCreate(
            ['shop_id' => $shopId],
            [
                'shop_url' => $apiUrl,
                'access_token' => $tokenData['access_token'],
                'is_active' => true,
                'installed_at' => now(),
                'uninstalled_at' => null,
                'metadata' => [
                    'return_url' => $returnUrl,
                    'access_token_url' => $accessTokenUrl,
                ],
            ]
        );

        // Create or update the polling Shop linked to this EpagesShop
        $shop = \App\Models\Shop::updateOrCreate(
            ['epages_shop_id' => $epagesShop->id],
            [
                'name' => $shopId,
                'shop_url' => $apiUrl,
                'epages_version' => 'now',
                'api_token' => $tokenData['access_token'],
                'polling_interval_minutes' => 5,
                'active' => true,
                'source' => 'appstore',
            ]
        );

        Log::info('ePages shop registered successfully', [
            'epages_shop_id' => $epagesShop->id,
            'shop_id' => $shop->id,
            'api_url' => $apiUrl,
        ]);

        // Store shop info in session and redirect to onboarding
        session([
            'epages_onboarding' => [
                'epages_shop_id' => $epagesShop->id,
                'shop_id' => $shop->id,
                'shop_name' => $shopId,
                'shop_url' => $epagesShop->shop_url,
                'return_url' => $returnUrl,
            ],
        ]);

        return redirect()->route('epages.onboarding.register');
    }

    public function unregister(Request $request)
    {
        Log::info('ePages uninstall request received', [
            'params' => $request->all(),
        ]);

        $apiUrl = $request->input('api_url');
        $shopId = $this->extractShopId($apiUrl);

        if (!$shopId) {
            return response()->json([
                'error' => 'Missing api_url',
            ], 400);
        }

        $epagesShop = EpagesShop::where('shop_id', $shopId)->first();

        if ($epagesShop) {
            $epagesShop->update([
                'is_active' => false,
                'uninstalled_at' => now(),
            ]);

            // Also deactivate the linked polling Shop
            \App\Models\Shop::where('epages_shop_id', $epagesShop->id)->update([
                'active' => false,
            ]);

            Log::info('ePages shop unregistered', [
                'epages_shop_id' => $epagesShop->id,
                'shop_id' => $shopId,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Shop unregistered successfully',
        ]);
    }

    protected function exchangeCodeForToken(string $code, string $accessTokenUrl): array
    {
        $response = Http::asForm()->post($accessTokenUrl, [
            'code' => $code,
            'client_id' => config('epages.client_id'),
            'client_secret' => config('epages.client_secret'),
        ]);

        if (!$response->successful()) {
            throw new \Exception('Token exchange failed: ' . $response->body());
        }

        return $response->json();
    }

    protected function verifySignature(string $code, string $accessTokenUrl, string $signature): bool
    {
        $clientSecret = config('epages.client_secret');

        if (!$clientSecret) {
            return true; // Skip verification if no secret configured
        }

        $data = $code . ':' . $accessTokenUrl;
        // ePages sends base64-encoded HMAC-SHA256 signature
        $expectedSignature = base64_encode(hash_hmac('sha256', $data, $clientSecret, true));

        return hash_equals($expectedSignature, $signature);
    }

    protected function extractShopId(string $apiUrl): string
    {
        // Extract shop identifier from API URL
        // e.g., https://shop-12345.epages.com/rs/shops/shop-12345 -> shop-12345
        $parsed = parse_url($apiUrl);

        if (preg_match('/shops\/([^\/]+)/', $apiUrl, $matches)) {
            return $matches[1];
        }

        return $parsed['host'] ?? md5($apiUrl);
    }
}
