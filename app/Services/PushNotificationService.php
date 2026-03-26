<?php

namespace App\Services;

use App\Models\Shop;
use App\Models\User;
use App\Models\PushSubscription;
use Minishlink\WebPush\WebPush;
use Minishlink\WebPush\Subscription;
use Illuminate\Support\Facades\Log;

class PushNotificationService
{
    private WebPush $webPush;

    public function __construct()
    {
        $this->webPush = new WebPush([
            'VAPID' => [
                'subject' => config('webpush.vapid.subject'),
                'publicKey' => config('webpush.vapid.public_key'),
                'privateKey' => config('webpush.vapid.private_key'),
            ],
        ]);
    }

    /**
     * Send a push notification to the owner of a shop.
     * Respects the per-shop toggle and tier limits.
     */
    public function sendToShopOwner(Shop $shop, array $payload): void
    {
        if (!$shop->push_notifications_enabled) {
            return;
        }

        $user = $shop->user;

        if (!$user || !$user->tierAllowsPushNotifications()) {
            return;
        }

        $subscriptions = PushSubscription::where('user_id', $user->id)->get();

        if ($subscriptions->isEmpty()) {
            return;
        }

        foreach ($subscriptions as $subscription) {
            $this->queueNotification($subscription, $payload);
        }

        $this->processResults();
    }

    private function queueNotification(PushSubscription $subscription, array $payload): void
    {
        $this->webPush->queueNotification(
            Subscription::create([
                'endpoint' => $subscription->endpoint,
                'keys' => [
                    'p256dh' => $subscription->p256dh_key,
                    'auth' => $subscription->auth_key,
                ],
            ]),
            json_encode($payload)
        );
    }

    private function processResults(): void
    {
        foreach ($this->webPush->flush() as $report) {
            $endpoint = $report->getRequest()->getUri()->__toString();

            if ($report->isSuccess()) {
                Log::info('Push notification sent', ['endpoint' => substr($endpoint, 0, 50)]);
            } else {
                Log::warning('Push notification failed', [
                    'endpoint' => substr($endpoint, 0, 50),
                    'reason' => $report->getReason(),
                ]);

                // Remove expired/invalid subscriptions
                if ($report->isSubscriptionExpired()) {
                    PushSubscription::where('endpoint_hash', hash('sha256', $endpoint))->delete();
                    Log::info('Removed expired push subscription');
                }
            }
        }
    }
}
