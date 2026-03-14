<?php

namespace EpagesIntegration\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EpagesShop extends Model
{
    protected $table = 'epages_shops';

    protected $fillable = [
        'user_id',
        'shop_id',
        'shop_url',
        'access_token',
        'refresh_token',
        'token_expires_at',
        'scopes',
        'is_active',
        'metadata',
        'installed_at',
        'uninstalled_at',
    ];

    protected $casts = [
        'scopes' => 'array',
        'metadata' => 'array',
        'is_active' => 'boolean',
        'token_expires_at' => 'datetime',
        'installed_at' => 'datetime',
        'uninstalled_at' => 'datetime',
    ];

    protected $hidden = [
        'access_token',
        'refresh_token',
    ];

    protected function accessToken(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value) => $value ? $this->decryptToken($value) : null,
            set: fn (?string $value) => $value ? $this->encryptToken($value) : null,
        );
    }

    protected function refreshToken(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value) => $value ? $this->decryptToken($value) : null,
            set: fn (?string $value) => $value ? $this->encryptToken($value) : null,
        );
    }

    protected function encryptToken(string $value): string
    {
        if (config('epages.encrypt_tokens', true)) {
            return encrypt($value);
        }

        return $value;
    }

    protected function decryptToken(string $value): string
    {
        if (config('epages.encrypt_tokens', true)) {
            try {
                return decrypt($value);
            } catch (\Exception $e) {
                return $value;
            }
        }

        return $value;
    }

    public function isTokenExpired(): bool
    {
        if (!$this->token_expires_at) {
            return false;
        }

        return $this->token_expires_at->isPast();
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByShopId($query, string $shopId)
    {
        return $query->where('shop_id', $shopId);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class);
    }
}
