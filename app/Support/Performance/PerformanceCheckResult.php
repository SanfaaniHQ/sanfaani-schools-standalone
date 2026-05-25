<?php

namespace App\Support\Performance;

class PerformanceCheckResult
{
    public function __construct(
        public string $key,
        public string $label,
        public string $status,
        public string $message,
        public string $severity = 'info',
        public array $context = [],
    ) {}

    public static function make(
        string $key,
        string $label,
        string $status,
        string $message,
        string $severity = 'info',
        array $context = [],
    ): self {
        return new self($key, $label, $status, $message, $severity, $context);
    }

    public static function pass(string $key, string $label, string $message, array $context = []): self
    {
        return self::make($key, $label, 'pass', $message, 'success', $context);
    }

    public static function warning(string $key, string $label, string $message, array $context = []): self
    {
        return self::make($key, $label, 'warning', $message, 'warning', $context);
    }

    public static function fail(string $key, string $label, string $message, array $context = []): self
    {
        return self::make($key, $label, 'fail', $message, 'danger', $context);
    }

    public static function info(string $key, string $label, string $message, array $context = []): self
    {
        return self::make($key, $label, 'info', $message, 'info', $context);
    }

    public function toArray(): array
    {
        return [
            'key' => $this->key,
            'label' => $this->label,
            'status' => $this->status,
            'severity' => $this->severity,
            'message' => $this->message,
            'context' => $this->context,
        ];
    }
}
