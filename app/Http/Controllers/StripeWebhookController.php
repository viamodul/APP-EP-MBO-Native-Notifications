<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\SubscriptionService;
use Illuminate\Support\Facades\Log;
use Laravel\Cashier\Http\Controllers\WebhookController as CashierController;
use Symfony\Component\HttpFoundation\Response;

class StripeWebhookController extends CashierController
{
    /**
     * Handle customer subscription created.
     */
    protected function handleCustomerSubscriptionCreated(array $payload): Response
    {
        // Let Cashier create the subscription record first
        $response = parent::handleCustomerSubscriptionCreated($payload);

        // Then sync our custom tier
        $user = $this->findUserByStripeId($payload['data']['object']['customer'] ?? null);

        if ($user) {
            Log::info('Stripe webhook: subscription created', [
                'user_id' => $user->id,
                'stripe_subscription_id' => $payload['data']['object']['id'] ?? null,
            ]);

            $this->getSubscriptionService()->syncTierFromStripe($user);
        }

        return $response;
    }

    /**
     * Handle customer subscription updated.
     */
    protected function handleCustomerSubscriptionUpdated(array $payload): Response
    {
        // Let Cashier update the subscription record first
        $response = parent::handleCustomerSubscriptionUpdated($payload);

        // Then sync our custom tier
        $user = $this->findUserByStripeId($payload['data']['object']['customer'] ?? null);

        if ($user) {
            Log::info('Stripe webhook: subscription updated', [
                'user_id' => $user->id,
                'stripe_subscription_id' => $payload['data']['object']['id'] ?? null,
                'status' => $payload['data']['object']['status'] ?? null,
            ]);

            $this->getSubscriptionService()->syncTierFromStripe($user);
        }

        return $response;
    }

    /**
     * Handle customer subscription deleted.
     */
    protected function handleCustomerSubscriptionDeleted(array $payload): Response
    {
        // Let Cashier handle the deletion first
        $response = parent::handleCustomerSubscriptionDeleted($payload);

        $user = $this->findUserByStripeId($payload['data']['object']['customer'] ?? null);

        if ($user) {
            Log::info('Stripe webhook: subscription deleted', [
                'user_id' => $user->id,
                'stripe_subscription_id' => $payload['data']['object']['id'] ?? null,
            ]);

            // Downgrade to trial (expired)
            $user->update([
                'subscription_tier' => 'trial',
                'trial_ends_at' => now(), // Trial already expired
            ]);
        }

        return $response;
    }

    /**
     * Handle invoice payment succeeded.
     */
    protected function handleInvoicePaymentSucceeded(array $payload): Response
    {
        // Let Cashier handle the invoice first
        $response = parent::handleInvoicePaymentSucceeded($payload);

        $user = $this->findUserByStripeId($payload['data']['object']['customer'] ?? null);

        if ($user) {
            Log::info('Stripe webhook: invoice payment succeeded', [
                'user_id' => $user->id,
                'invoice_id' => $payload['data']['object']['id'] ?? null,
                'amount_paid' => $payload['data']['object']['amount_paid'] ?? 0,
            ]);

            // Reset billing period counters
            $this->getSubscriptionService()->handleInvoicePaid($user);
        }

        return $response;
    }

    /**
     * Find user by Stripe customer ID.
     */
    protected function findUserByStripeId(?string $stripeId): ?User
    {
        if (!$stripeId) {
            return null;
        }

        return User::where('stripe_id', $stripeId)->first();
    }

    /**
     * Get the subscription service instance.
     */
    protected function getSubscriptionService(): SubscriptionService
    {
        return new SubscriptionService();
    }
}
