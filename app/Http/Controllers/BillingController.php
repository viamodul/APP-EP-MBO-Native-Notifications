<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class BillingController extends Controller
{
    /**
     * Show the billing page with current plan and usage.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        // Get visible tiers for display
        $tiers = collect(config('subscription.tiers'))
            ->filter(fn($tier) => $tier['visible'] ?? true)
            ->except('trial'); // Don't show trial as an option to subscribe

        return view('billing.index', [
            'user' => $user,
            'tiers' => $tiers,
            'currentTier' => $user->subscription_tier,
            'tierConfig' => $user->getTierConfig(),
            'usagePercentage' => $user->getUsagePercentage(),
            'webhooksSent' => $user->webhooks_sent_this_period,
            'webhooksLimit' => $user->getWebhooksLimit(),
            'remainingWebhooks' => $user->getRemainingWebhooks(),
            'isOnTrial' => $user->isOnTrial(),
            'trialExpired' => $user->trialExpired(),
            'daysUntilTrialExpires' => $user->daysUntilTrialExpires(),
        ]);
    }

    /**
     * Redirect to Stripe Customer Portal.
     */
    public function portal(Request $request)
    {
        $user = $request->user();

        // Create Stripe customer if doesn't exist
        if (!$user->stripe_id) {
            $user->createAsStripeCustomer();
        }

        return $user->redirectToBillingPortal(route('billing.index'));
    }

    /**
     * Create a Stripe Checkout session for a new subscription.
     */
    public function checkout(Request $request)
    {
        $request->validate([
            'tier' => 'required|string|in:starter,pro,business',
            'interval' => 'required|string|in:monthly,yearly',
        ]);

        $user = $request->user();
        $tier = $request->input('tier');
        $interval = $request->input('interval');

        $priceId = config("subscription.tiers.{$tier}.stripe_prices.{$interval}");

        if (!$priceId) {
            Log::error('Invalid Stripe price ID', [
                'tier' => $tier,
                'interval' => $interval,
            ]);
            return back()->with('error', 'Invalid plan selected. Please try again.');
        }

        // Create Stripe customer if doesn't exist
        if (!$user->stripe_id) {
            $user->createAsStripeCustomer();
        }

        return $user->newSubscription('default', $priceId)
            ->checkout([
                'success_url' => route('billing.index') . '?checkout=success',
                'cancel_url' => route('billing.index') . '?checkout=cancelled',
            ]);
    }

    /**
     * Cancel the current subscription.
     */
    public function cancel(Request $request)
    {
        $user = $request->user();
        $subscription = $user->subscription('default');

        if ($subscription && $subscription->active()) {
            $subscription->cancel();

            return back()->with('success', 'Your subscription has been cancelled. You will have access until the end of your billing period.');
        }

        return back()->with('error', 'No active subscription to cancel.');
    }

    /**
     * Resume a cancelled subscription.
     */
    public function resume(Request $request)
    {
        $user = $request->user();
        $subscription = $user->subscription('default');

        if ($subscription && $subscription->onGracePeriod()) {
            $subscription->resume();

            return back()->with('success', 'Your subscription has been resumed.');
        }

        return back()->with('error', 'Unable to resume subscription.');
    }
}
