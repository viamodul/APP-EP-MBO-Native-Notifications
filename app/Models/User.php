<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Cashier\Billable;

class User extends Authenticatable
{
    use HasFactory, Notifiable, Billable;

    public function shops(): HasMany
    {
        return $this->hasMany(Shop::class);
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'subscription_tier',
        'webhooks_sent_this_period',
        'billing_period_started_at',
        'billing_period_ends_at',
        'last_usage_alert_threshold',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'trial_ends_at' => 'datetime',
            'billing_period_started_at' => 'datetime',
            'billing_period_ends_at' => 'datetime',
            'webhooks_sent_this_period' => 'integer',
            'last_usage_alert_threshold' => 'integer',
        ];
    }

    /**
     * Get the configuration for the user's subscription tier.
     */
    public function getTierConfig(): array
    {
        $tier = $this->subscription_tier ?? config('subscription.default_tier');
        return config("subscription.tiers.{$tier}", config('subscription.tiers.trial'));
    }

    /**
     * Get the webhooks limit for the user's tier.
     */
    public function getWebhooksLimit(): ?int
    {
        return $this->getTierConfig()['webhooks_limit'];
    }

    /**
     * Get the shops limit for the user's tier.
     */
    public function getShopsLimit(): ?int
    {
        return $this->getTierConfig()['shops_limit'];
    }

    /**
     * Get the log retention days for the user's tier.
     */
    public function getLogRetentionDays(): int
    {
        return $this->getTierConfig()['log_retention_days'];
    }

    /**
     * Get the polling interval in minutes for the user's tier.
     */
    public function getPollingIntervalMinutes(): int
    {
        return $this->getTierConfig()['polling_interval_minutes'];
    }

    /**
     * Check if the user can send more webhooks.
     */
    public function canSendWebhook(): bool
    {
        $limit = $this->getWebhooksLimit();

        // Unlimited webhooks (null limit)
        if ($limit === null) {
            return true;
        }

        return $this->webhooks_sent_this_period < $limit;
    }

    /**
     * Check if the user can add more shops.
     */
    public function canAddShop(): bool
    {
        $limit = $this->getShopsLimit();

        // Unlimited shops (null limit)
        if ($limit === null) {
            return true;
        }

        return $this->shops()->count() < $limit;
    }

    /**
     * Get the webhook usage percentage.
     */
    public function getUsagePercentage(): ?float
    {
        $limit = $this->getWebhooksLimit();

        // Unlimited webhooks (null limit)
        if ($limit === null) {
            return null;
        }

        if ($limit === 0) {
            return 100;
        }

        return round(($this->webhooks_sent_this_period / $limit) * 100, 1);
    }

    /**
     * Get the remaining webhooks for this period.
     */
    public function getRemainingWebhooks(): ?int
    {
        $limit = $this->getWebhooksLimit();

        // Unlimited webhooks (null limit)
        if ($limit === null) {
            return null;
        }

        return max(0, $limit - $this->webhooks_sent_this_period);
    }

    /**
     * Increment the webhook counter.
     */
    public function incrementWebhookCount(): void
    {
        $this->increment('webhooks_sent_this_period');
    }

    /**
     * Reset the billing period counters.
     */
    public function resetBillingPeriod(): void
    {
        $this->update([
            'webhooks_sent_this_period' => 0,
            'billing_period_started_at' => now(),
            'billing_period_ends_at' => now()->addMonth(),
            'last_usage_alert_threshold' => null,
        ]);
    }

    /**
     * Check if user is on trial.
     */
    public function isOnTrial(): bool
    {
        return $this->subscription_tier === 'trial' && $this->trial_ends_at && $this->trial_ends_at->isFuture();
    }

    /**
     * Check if the trial has expired.
     */
    public function trialExpired(): bool
    {
        return $this->subscription_tier === 'trial' && $this->trial_ends_at && $this->trial_ends_at->isPast();
    }

    /**
     * Get the number of days until trial expires.
     */
    public function daysUntilTrialExpires(): ?int
    {
        if (!$this->isOnTrial()) {
            return null;
        }

        return max(0, now()->diffInDays($this->trial_ends_at, false));
    }

    /**
     * Check if this is a paid tier (not trial or dev).
     */
    public function isOnPaidTier(): bool
    {
        return !in_array($this->subscription_tier, ['trial', 'dev']);
    }

    /**
     * Check if this is the dev tier.
     */
    public function isDevTier(): bool
    {
        return $this->subscription_tier === 'dev';
    }

    /**
     * Get the human-readable tier name.
     */
    public function getTierName(): string
    {
        return $this->getTierConfig()['name'];
    }
}
