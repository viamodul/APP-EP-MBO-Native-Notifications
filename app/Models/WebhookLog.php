<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WebhookLog extends Model
{
    protected $fillable = [
        'shop_id',
        'order_id',
        'order_created_at',
        'order_data',
        'webhook_payload',
        'webhook_url',
        'response_status',
        'response_body',
        'status',
        'retry_count',
        'sent_at',
    ];

    protected $casts = [
        'order_created_at' => 'datetime',
        'order_data' => 'json',
        'webhook_payload' => 'json',
        'sent_at' => 'datetime',
    ];

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }
}
