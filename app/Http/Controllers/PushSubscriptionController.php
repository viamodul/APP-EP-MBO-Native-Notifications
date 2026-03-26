<?php

namespace App\Http\Controllers;

use App\Models\PushSubscription;
use App\Models\Shop;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class PushSubscriptionController extends Controller
{
    public function getVapidPublicKey(): JsonResponse
    {
        return response()->json(['public_key' => config('webpush.vapid.public_key')]);
    }

    public function subscribe(Request $request): JsonResponse
    {
        $request->validate([
            'endpoint' => ['required', 'string'],
            'keys.p256dh' => ['required', 'string'],
            'keys.auth' => ['required', 'string'],
        ]);

        $user = Auth::user();
        $endpointHash = hash('sha256', $request->endpoint);

        PushSubscription::updateOrCreate(
            ['endpoint_hash' => $endpointHash],
            [
                'user_id' => $user->id,
                'endpoint' => $request->endpoint,
                'p256dh_key' => $request->input('keys.p256dh'),
                'auth_key' => $request->input('keys.auth'),
                'user_agent' => $request->userAgent(),
            ]
        );

        return response()->json(['status' => 'subscribed']);
    }

    public function unsubscribe(Request $request): JsonResponse
    {
        $request->validate(['endpoint' => ['required', 'string']]);

        PushSubscription::where('endpoint_hash', hash('sha256', $request->endpoint))
            ->where('user_id', Auth::id())
            ->delete();

        return response()->json(['status' => 'unsubscribed']);
    }

    public function checkSubscription(Request $request): JsonResponse
    {
        $request->validate(['endpoint' => ['required', 'string']]);

        $exists = PushSubscription::where('endpoint_hash', hash('sha256', $request->endpoint))
            ->where('user_id', Auth::id())
            ->exists();

        return response()->json(['subscribed' => $exists]);
    }

    public function toggleShopPush(Request $request, Shop $shop): JsonResponse
    {
        if ($shop->user_id !== Auth::id()) {
            abort(403);
        }

        $request->validate(['enabled' => ['required', 'boolean']]);

        $user = Auth::user();

        if ($request->enabled && !$user->tierAllowsPushNotifications()) {
            return response()->json([
                'error' => 'Push notifications are not available on your current plan.',
            ], 403);
        }

        $shop->update(['push_notifications_enabled' => $request->enabled]);

        return response()->json(['enabled' => $shop->push_notifications_enabled]);
    }
}
