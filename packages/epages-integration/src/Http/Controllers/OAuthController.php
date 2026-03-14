<?php

namespace EpagesIntegration\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use EpagesIntegration\Services\OAuthService;

class OAuthController extends Controller
{
    protected OAuthService $oauthService;

    public function __construct(OAuthService $oauthService)
    {
        $this->oauthService = $oauthService;
    }

    public function redirect(Request $request)
    {
        $request->validate([
            'shop_url' => 'required|url',
        ]);

        $shopUrl = $request->input('shop_url');
        $authUrl = $this->oauthService->getAuthorizationUrl($shopUrl);

        session(['epages_shop_url' => $shopUrl]);

        return redirect($authUrl);
    }

    public function callback(Request $request)
    {
        $request->validate([
            'code' => 'required|string',
            'state' => 'required|string',
        ]);

        $storedState = session('epages_oauth_state');
        if ($request->input('state') !== $storedState) {
            return redirect()->route('epages.install')
                ->withErrors(['oauth' => 'Invalid state parameter. Please try again.']);
        }

        $shopUrl = session('epages_shop_url');
        if (!$shopUrl) {
            return redirect()->route('epages.install')
                ->withErrors(['oauth' => 'Shop URL not found in session. Please try again.']);
        }

        try {
            $tokenData = $this->oauthService->exchangeCodeForToken(
                $request->input('code'),
                $shopUrl
            );

            $shopId = $this->extractShopId($shopUrl, $tokenData);

            $shop = $this->oauthService->storeCredentials($shopId, $shopUrl, $tokenData);

            session()->forget(['epages_oauth_state', 'epages_shop_url']);

            return redirect()->route('epages.success')
                ->with('shop', $shop)
                ->with('message', 'Successfully connected to ePages shop!');

        } catch (\Exception $e) {
            report($e);

            return redirect()->route('epages.install')
                ->withErrors(['oauth' => 'Failed to connect: ' . $e->getMessage()]);
        }
    }

    public function disconnect(Request $request)
    {
        $request->validate([
            'shop_id' => 'required|string',
        ]);

        $shop = \EpagesIntegration\Models\EpagesShop::where('shop_id', $request->input('shop_id'))->first();

        if (!$shop) {
            return back()->withErrors(['shop' => 'Shop not found.']);
        }

        $this->oauthService->revokeToken($shop);

        return redirect()->route('epages.install')
            ->with('message', 'Successfully disconnected from ePages shop.');
    }

    protected function extractShopId(string $shopUrl, array $tokenData): string
    {
        if (isset($tokenData['shop_id'])) {
            return $tokenData['shop_id'];
        }

        $parsed = parse_url($shopUrl);
        return $parsed['host'] ?? md5($shopUrl);
    }
}
