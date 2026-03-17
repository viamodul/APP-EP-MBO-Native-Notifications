<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\SubscriptionService;
use Illuminate\Console\Command;

class SetUserTier extends Command
{
    protected $signature = 'user:set-tier {email} {tier}';

    protected $description = 'Manually set a user\'s subscription tier (useful for dev/admin users)';

    public function handle(SubscriptionService $subscriptionService): int
    {
        $email = $this->argument('email');
        $tier = $this->argument('tier');

        // Validate tier
        $validTiers = array_keys(config('subscription.tiers', []));
        if (!in_array($tier, $validTiers)) {
            $this->error("Invalid tier: {$tier}");
            $this->info('Valid tiers: ' . implode(', ', $validTiers));
            return self::FAILURE;
        }

        // Find user
        $user = User::where('email', $email)->first();
        if (!$user) {
            $this->error("User not found: {$email}");
            return self::FAILURE;
        }

        $oldTier = $user->subscription_tier;

        try {
            $subscriptionService->upgradeTier($user, $tier);

            $this->info("Updated user {$email}:");
            $this->line("  Old tier: {$oldTier}");
            $this->line("  New tier: {$tier}");

            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->error("Error: {$e->getMessage()}");
            return self::FAILURE;
        }
    }
}
