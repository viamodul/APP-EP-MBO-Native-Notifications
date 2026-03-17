<?php

namespace App\Services;

use App\Models\User;
use App\Notifications\UsageThresholdReached;
use App\Notifications\TrialExpiringSoon;
use Illuminate\Support\Facades\Log;
use Laravel\Cashier\Subscription;

class SubscriptionService
{
    /**
     * Check if user can send webhook and increment counter if allowed.
     * Returns true if webhook was allowed, false if blocked.
     */
    public function checkAndIncrementWebhookUsage(User $user): bool
    {
        // Check if trial has expired
        if ($user->trialExpired()) {
            Log::info('Webhook blocked: trial expired', ['user_id' => $user->id]);
            return false;
        }

        // Check if user can send webhook
        if (!$user->canSendWebhook()) {
            Log::info('Webhook blocked: limit reached', [
                'user_id' => $user->id,
                'tier' => $user->subscription_tier,
                'sent' => $user->webhooks_sent_this_period,
                'limit' => $user->getWebhooksLimit(),
            ]);
            return false;
        }

        // Increment counter
        $user->incrementWebhookCount();

        // Check usage thresholds after incrementing
        $this->checkUsageThresholds($user);

        return true;
    }

    /**
     * Check usage thresholds and send notifications.
     */
    public function checkUsageThresholds(User $user): void
    {
        $percentage = $user->getUsagePercentage();

        // Unlimited usage (null percentage)
        if ($percentage === null) {
            return;
        }

        $thresholds = config('subscription.usage_alert_thresholds', [50, 75, 90, 100]);
        $lastThreshold = $user->last_usage_alert_threshold;

        foreach ($thresholds as $threshold) {
            // Check if we've crossed this threshold and haven't alerted for it yet
            if ($percentage >= $threshold && ($lastThreshold === null || $threshold > $lastThreshold)) {
                $this->sendUsageAlert($user, $threshold);
                $user->update(['last_usage_alert_threshold' => $threshold]);
                break; // Only send one notification at a time
            }
        }
    }

    /**
     * Send usage threshold notification.
     */
    protected function sendUsageAlert(User $user, int $threshold): void
    {
        Log::info('Sending usage alert', [
            'user_id' => $user->id,
            'threshold' => $threshold,
            'usage' => $user->webhooks_sent_this_period,
            'limit' => $user->getWebhooksLimit(),
        ]);

        $user->notify(new UsageThresholdReached($threshold));
    }

    /**
     * Sync the subscription tier from Stripe.
     */
    public function syncTierFromStripe(User $user): void
    {
        $subscription = $user->subscription('default');

        if (!$subscription || !$subscription->active()) {
            // No active subscription - check if was on paid tier
            if ($user->isOnPaidTier()) {
                Log::info('User subscription ended, downgrading to trial', ['user_id' => $user->id]);
                $user->update([
                    'subscription_tier' => 'trial',
                    'trial_ends_at' => now(), // Trial already expired
                ]);
            }
            return;
        }

        // Determine tier from Stripe price
        $tier = $this->getTierFromSubscription($subscription);

        if ($tier && $tier !== $user->subscription_tier) {
            Log::info('Syncing subscription tier from Stripe', [
                'user_id' => $user->id,
                'old_tier' => $user->subscription_tier,
                'new_tier' => $tier,
            ]);

            $user->update(['subscription_tier' => $tier]);
        }
    }

    /**
     * Determine the tier from a Stripe subscription.
     */
    protected function getTierFromSubscription(Subscription $subscription): ?string
    {
        $priceId = $subscription->stripe_price;

        // Check each tier's price IDs
        foreach (config('subscription.tiers') as $tierKey => $tierConfig) {
            if (!isset($tierConfig['stripe_prices'])) {
                continue;
            }

            foreach ($tierConfig['stripe_prices'] as $interval => $configPriceId) {
                if ($priceId === $configPriceId) {
                    return $tierKey;
                }
            }
        }

        return null;
    }

    /**
     * Initialize trial for a new user.
     */
    public function initializeTrial(User $user): void
    {
        $trialDays = config('subscription.tiers.trial.trial_days', 14);

        $user->update([
            'subscription_tier' => 'trial',
            'trial_ends_at' => now()->addDays($trialDays),
            'webhooks_sent_this_period' => 0,
            'billing_period_started_at' => now(),
            'billing_period_ends_at' => now()->addDays($trialDays),
            'last_usage_alert_threshold' => null,
        ]);

        Log::info('Trial initialized for user', [
            'user_id' => $user->id,
            'trial_ends_at' => $user->trial_ends_at,
        ]);
    }

    /**
     * Handle successful invoice payment - reset billing period.
     */
    public function handleInvoicePaid(User $user): void
    {
        Log::info('Resetting billing period after payment', ['user_id' => $user->id]);
        $user->resetBillingPeriod();
    }

    /**
     * Get users with expiring trials.
     */
    public function getUsersWithExpiringTrials(int $daysUntilExpiry): \Illuminate\Database\Eloquent\Collection
    {
        $targetDate = now()->addDays($daysUntilExpiry)->startOfDay();

        return User::where('subscription_tier', 'trial')
            ->whereNotNull('trial_ends_at')
            ->whereDate('trial_ends_at', $targetDate)
            ->get();
    }

    /**
     * Send trial expiring notification.
     */
    public function sendTrialExpiringNotification(User $user, int $daysRemaining): void
    {
        Log::info('Sending trial expiring notification', [
            'user_id' => $user->id,
            'days_remaining' => $daysRemaining,
        ]);

        $user->notify(new TrialExpiringSoon($daysRemaining));
    }

    /**
     * Upgrade a user to a specific tier (for admin/manual use).
     */
    public function upgradeTier(User $user, string $tier): void
    {
        if (!array_key_exists($tier, config('subscription.tiers'))) {
            throw new \InvalidArgumentException("Invalid tier: {$tier}");
        }

        $user->update([
            'subscription_tier' => $tier,
            'trial_ends_at' => null,
        ]);

        // Reset billing period on upgrade
        $user->resetBillingPeriod();

        Log::info('User tier upgraded manually', [
            'user_id' => $user->id,
            'tier' => $tier,
        ]);
    }
}
