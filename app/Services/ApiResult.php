<?php

namespace App\Services;

class ApiResult
{
    public const STATUS_SUCCESS = 'success';
    public const STATUS_SHOP_NOT_FOUND = 'shop_not_found';
    public const STATUS_UNAUTHORIZED = 'unauthorized';
    public const STATUS_TIMEOUT = 'timeout';
    public const STATUS_CONNECTION_ERROR = 'connection_error';
    public const STATUS_SERVER_ERROR = 'server_error';
    public const STATUS_UNKNOWN_ERROR = 'unknown_error';

    public function __construct(
        public readonly string $status,
        public readonly mixed $data = null,
        public readonly ?string $message = null,
        public readonly ?int $httpStatus = null,
    ) {}

    public function isSuccess(): bool
    {
        return $this->status === self::STATUS_SUCCESS;
    }

    public function isShopUnavailable(): bool
    {
        return in_array($this->status, [
            self::STATUS_SHOP_NOT_FOUND,
            self::STATUS_UNAUTHORIZED,
            self::STATUS_TIMEOUT,
            self::STATUS_CONNECTION_ERROR,
        ]);
    }

    public function getFailureReason(): string
    {
        return match ($this->status) {
            self::STATUS_SHOP_NOT_FOUND => 'Shop not found (404)',
            self::STATUS_UNAUTHORIZED => 'Unauthorized - invalid or expired token (401/403)',
            self::STATUS_TIMEOUT => 'Connection timeout',
            self::STATUS_CONNECTION_ERROR => 'Connection error - shop unreachable',
            self::STATUS_SERVER_ERROR => "Server error (HTTP {$this->httpStatus})",
            self::STATUS_UNKNOWN_ERROR => $this->message ?? 'Unknown error',
            default => $this->message ?? 'Unknown error',
        };
    }

    public static function success(mixed $data): self
    {
        return new self(self::STATUS_SUCCESS, $data);
    }

    public static function fromHttpStatus(int $status, ?string $body = null): self
    {
        return match (true) {
            $status === 404 => new self(self::STATUS_SHOP_NOT_FOUND, httpStatus: $status),
            $status === 401 || $status === 403 => new self(self::STATUS_UNAUTHORIZED, httpStatus: $status),
            $status >= 500 => new self(self::STATUS_SERVER_ERROR, message: $body, httpStatus: $status),
            default => new self(self::STATUS_UNKNOWN_ERROR, message: "HTTP {$status}: {$body}", httpStatus: $status),
        };
    }

    public static function timeout(): self
    {
        return new self(self::STATUS_TIMEOUT, message: 'Request timed out');
    }

    public static function connectionError(string $message): self
    {
        return new self(self::STATUS_CONNECTION_ERROR, message: $message);
    }
}
