<?php

namespace App\Support\Branding;

class BrandingCheckResult
{
    public function __construct(
        public readonly string $key,
        public readonly string $status,
        public readonly string $message,
        public readonly array $context = [],
    ) {}

    public function ok(): bool
    {
        return $this->status === 'pass';
    }

    public function warning(): bool
    {
        return $this->status === 'warning';
    }

    public function failed(): bool
    {
        return $this->status === 'fail';
    }

    public function toArray(): array
    {
        return [
            'key' => $this->key,
            'status' => $this->status,
            'message' => $this->message,
            'context' => $this->context,
        ];
    }
}
