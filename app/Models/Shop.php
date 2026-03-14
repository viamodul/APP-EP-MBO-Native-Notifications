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
        'source',
        'group_name',
    ];

    protected $casts = [
        'last_order_check' => 'datetime',
        'last_processed_order_date' => 'datetime',
        'active' => 'boolean',
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
        //JUST for test pourposes
        return true;
        if (!$this->active) {
            return false;
        }

        if (!$this->last_order_check) {
            return true;
        }

        return $this->last_order_check->addMinutes($this->polling_interval_minutes) <= now();
    }
}
