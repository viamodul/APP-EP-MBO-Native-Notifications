<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use EpagesIntegration\Models\EpagesShop;

class Shop extends Model
{
    protected $fillable = [
        'user_id',
        'epages_shop_id',
        'name',
        'shop_url',
        'epages_version',
        'api_token',
        'webhook_url',
        'polling_interval_minutes',
        'last_order_check',
        'last_processed_order_date',
        'active',
        'push_notifications_enabled',
        'api_failure_count',
        'api_last_failure_at',
        'api_failure_reason',
        'deactivated_at',
        'source',
        'group_name',
    ];

    protected $casts = [
        'last_order_check' => 'datetime',
        'last_processed_order_date' => 'datetime',
        'active' => 'boolean',
        'push_notifications_enabled' => 'boolean',
        'api_last_failure_at' => 'datetime',
        'deactivated_at' => 'datetime',
        //'api_token' => 'encrypted',
    ];

    public function webhookLogs(): HasMany
    {
        return $this->hasMany(WebhookLog::class);
    }

    public function epagesShop(): BelongsTo
    {
        return $this->belongsTo(EpagesShop::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getWebhookUrlAttribute($value): string
    {
        return $value ?? config('services.epages.webhook_url');
    }

    public function shouldPoll(): bool
    {
        // In local/development, always poll (ignores interval for easier testing)
        if (app()->isLocal()) {
            return true;
        }

        if (!$this->active) {
            return false;
        }

        if (!$this->last_order_check) {
            return true;
        }

        return $this->last_order_check->addMinutes($this->polling_interval_minutes) <= now();
    }

    /**
     * Record an API failure and deactivate shop if threshold reached.
     */
    public function recordApiFailure(string $reason, int $maxFailures = 3): bool
    {
        $this->increment('api_failure_count');
        $this->update([
            'api_last_failure_at' => now(),
            'api_failure_reason' => $reason,
        ]);

        if ($this->api_failure_count >= $maxFailures) {
            $this->deactivate("Auto-deactivated after {$maxFailures} consecutive API failures: {$reason}");
            return true; // Shop was deactivated
        }

        return false; // Shop still active
    }

    /**
     * Reset API failure count on successful connection.
     */
    public function recordApiSuccess(): void
    {
        if ($this->api_failure_count > 0) {
            $this->update([
                'api_failure_count' => 0,
                'api_failure_reason' => null,
            ]);
        }
    }

    /**
     * Deactivate the shop.
     */
    public function deactivate(string $reason): void
    {
        $this->update([
            'active' => false,
            'deactivated_at' => now(),
            'api_failure_reason' => $reason,
        ]);
    }

    /**
     * Reactivate the shop and reset failure tracking.
     */
    public function reactivate(): void
    {
        $this->update([
            'active' => true,
            'api_failure_count' => 0,
            'api_failure_reason' => null,
            'deactivated_at' => null,
        ]);
    }

    /**
     * Check if shop was auto-deactivated due to API failures.
     */
    public function wasAutoDeactivated(): bool
    {
        return !$this->active && $this->deactivated_at !== null;
    }
}
