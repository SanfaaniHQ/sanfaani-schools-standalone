<?php

namespace App\Support\Licensing;

use App\Models\License;

class LicenseValidationResult
{
    public function __construct(
        public readonly bool $valid,
        public readonly string $status,
        public readonly string $message,
        public readonly ?License $license = null,
        public readonly string $severity = 'info',
        public readonly array $context = [],
    ) {}

    public function valid(): bool
    {
        return $this->valid;
    }

    public function invalid(): bool
    {
        return ! $this->valid;
    }
}
