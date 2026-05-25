<?php

namespace App\Services\Security;

use Illuminate\Support\Str;
use Throwable;

class SecretRedactionService
{
    public function redact(Throwable|string|null $value, int $limit = 1000): ?string
    {
        if (! filled($value)) {
            return null;
        }

        $message = $value instanceof Throwable ? $value->getMessage() : (string) $value;

        $message = str_replace(base_path(), '[app]', $message);
        $message = str_replace(storage_path(), '[storage]', $message);
        $message = preg_replace('/\b([A-Z0-9_]*(?:PASSWORD|PASSWD|PWD|SECRET|TOKEN|KEY|API[_-]?KEY|ACCESS[_-]?KEY|PRIVATE[_-]?KEY|LICENSE[_-]?KEY|SMTP[_-]?PASS)[A-Z0-9_]*)\s*=\s*([^\s&]+)/i', '$1=[redacted]', $message) ?? $message;
        $message = preg_replace('/(Authorization:\s*)(Bearer|Basic)\s+[A-Za-z0-9+\/=._-]+/i', '$1$2 [redacted]', $message) ?? $message;
        $message = preg_replace('/(mysql|pgsql|postgres|redis):\/\/([^:\s\/]+):([^@\s]+)@/i', '$1://$2:[redacted]@', $message) ?? $message;
        $message = preg_replace('/[A-Z]:\\\\[^\s\'"]+/i', '[path]', $message) ?? $message;
        $message = preg_replace('#/(home|var|srv|www|storage|app|vendor)/[^\s\'"]+#i', '[path]', $message) ?? $message;
        $message = preg_replace('/\s+/', ' ', $message) ?? $message;

        return Str::limit(trim($message), $limit, '');
    }

    public function redactArray(array $context): array
    {
        $sanitized = [];

        foreach ($context as $key => $value) {
            $key = (string) $key;

            if ($this->isSensitiveKey($key)) {
                $sanitized[$key] = '[redacted]';
                continue;
            }

            $sanitized[$key] = match (true) {
                is_array($value) => $this->redactArray($value),
                is_string($value) => $this->redact($value),
                default => $value,
            };
        }

        return $sanitized;
    }

    public function isSensitiveKey(string $key): bool
    {
        return preg_match('/(password|passwd|pwd|secret|token|key|api[_-]?key|access[_-]?key|private[_-]?key|license[_-]?key|env|dsn|credential|authorization)/i', $key) === 1;
    }
}
