<?php

namespace App\Services\Licensing;

use Carbon\CarbonImmutable;
use Illuminate\Support\Str;
use JsonException;
use RuntimeException;

class SignedLicenseKeyService
{
    private const PREFIX = 'SLS1';

    private const ALPHABET = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';

    /**
     * @return array{license_key: string, payload: array<string, mixed>}
     */
    public function generate(array $data): array
    {
        $payload = $this->payload($data);
        $encodedPayload = $this->base32Encode($this->json($payload));
        $signature = $this->signature($encodedPayload);

        return [
            'license_key' => self::PREFIX.'-'.$encodedPayload.'-'.$signature,
            'payload' => $payload,
        ];
    }

    public function isSigned(string $licenseKey): bool
    {
        return str_starts_with(strtoupper(trim($licenseKey)), self::PREFIX.'-');
    }

    /**
     * @return array<string, mixed>
     */
    public function verify(string $licenseKey): array
    {
        $licenseKey = strtoupper(trim($licenseKey));

        if (! $this->isSigned($licenseKey)) {
            throw new RuntimeException('The license key is not a signed Sanfaani license key.');
        }

        $withoutPrefix = substr($licenseKey, strlen(self::PREFIX) + 1);
        $separator = strrpos($withoutPrefix, '-');

        if ($separator === false) {
            throw new RuntimeException('The signed license key format is invalid.');
        }

        $encodedPayload = substr($withoutPrefix, 0, $separator);
        $signature = strtolower(substr($withoutPrefix, $separator + 1));

        if ($encodedPayload === '' || $signature === '' || ! preg_match('/^[a-f0-9]{64}$/', $signature)) {
            throw new RuntimeException('The signed license key format is invalid.');
        }

        if (! hash_equals($this->signature($encodedPayload), $signature)) {
            throw new RuntimeException('The signed license key signature is invalid.');
        }

        try {
            $payload = json_decode($this->base32Decode($encodedPayload), true, flags: JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            throw new RuntimeException('The signed license payload is invalid.');
        }

        if (! is_array($payload)) {
            throw new RuntimeException('The signed license payload is invalid.');
        }

        $payload = $this->validatePayload($payload);
        $this->assertNotExpired($payload);

        return $payload;
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(array $data): array
    {
        $type = $this->normalizeType((string) ($data['type'] ?? 'annual'));
        $school = trim((string) ($data['school'] ?? ''));
        $domain = $this->normalizeDomain((string) ($data['domain'] ?? ''));
        $startsAt = $this->date((string) ($data['starts'] ?? now()->toDateString()), 'starts');
        $expiresAt = filled($data['expires'] ?? null) ? $this->date((string) $data['expires'], 'expires') : null;

        if ($school === '') {
            throw new RuntimeException('The school name is required.');
        }

        if ($domain === '') {
            throw new RuntimeException('The license domain is required.');
        }

        if (in_array($type, ['annual', 'trial', 'demo'], true) && $expiresAt === null) {
            throw new RuntimeException("The {$type} license type requires an expiry date.");
        }

        if ($expiresAt !== null && $this->dateValue($expiresAt)->lessThan($this->dateValue($startsAt))) {
            throw new RuntimeException('The expiry date must be on or after the start date.');
        }

        $maxSchools = max(1, (int) ($data['max_schools'] ?? 1));
        $maxUsers = filled($data['max_users'] ?? null) ? max(1, (int) $data['max_users']) : null;
        $maxStudents = filled($data['max_students'] ?? null) ? max(1, (int) $data['max_students']) : null;

        return [
            'version' => 1,
            'license_id' => (string) Str::ulid(),
            'type' => $type,
            'status' => $this->statusForType($type),
            'school' => $school,
            'domain' => $domain,
            'allowed_domains' => [$domain],
            'starts_at' => $startsAt,
            'expires_at' => $type === 'lifetime' ? null : $expiresAt,
            'issued_at' => CarbonImmutable::now('UTC')->toDateString(),
            'issued_by' => trim((string) ($data['issued_by'] ?? 'Sanfaani')) ?: 'Sanfaani',
            'entitlements' => $this->entitlements($data['entitlements'] ?? []),
            'limits' => [
                'max_schools' => $maxSchools,
                'max_users' => $maxUsers,
                'max_students' => $maxStudents,
            ],
            'notes' => filled($data['notes'] ?? null) ? trim((string) $data['notes']) : null,
        ];
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    private function validatePayload(array $payload): array
    {
        $payload['type'] = $this->normalizeType((string) ($payload['type'] ?? ''));
        $payload['status'] = (string) ($payload['status'] ?? $this->statusForType($payload['type']));
        $payload['school'] = trim((string) ($payload['school'] ?? ''));
        $payload['domain'] = $this->normalizeDomain((string) ($payload['domain'] ?? ''));
        $payload['starts_at'] = $this->date((string) ($payload['starts_at'] ?? ''), 'starts_at');
        $payload['expires_at'] = filled($payload['expires_at'] ?? null)
            ? $this->date((string) $payload['expires_at'], 'expires_at')
            : null;
        $payload['allowed_domains'] = collect($payload['allowed_domains'] ?? [$payload['domain']])
            ->map(fn (mixed $domain): string => $this->normalizeDomain((string) $domain))
            ->filter()
            ->unique()
            ->values()
            ->all();
        $payload['entitlements'] = $this->entitlements($payload['entitlements'] ?? []);

        if ($payload['school'] === '' || $payload['domain'] === '') {
            throw new RuntimeException('The signed license payload is missing required customer details.');
        }

        if (in_array($payload['type'], ['annual', 'trial', 'demo'], true) && $payload['expires_at'] === null) {
            throw new RuntimeException('The signed license payload is missing a required expiry date.');
        }

        return $payload;
    }

    private function normalizeType(string $type): string
    {
        $type = str($type)->trim()->lower()->replace(['-', ' '], '_')->toString();
        $type = $type === 'managed' ? 'managed_contract' : $type;

        if (! in_array($type, config('licensing.types', []), true)) {
            throw new RuntimeException('Unsupported license type.');
        }

        return $type;
    }

    private function statusForType(string $type): string
    {
        return match ($type) {
            'trial' => 'trial',
            'demo' => 'demo',
            default => 'active',
        };
    }

    /**
     * @return array<string, bool>
     */
    private function entitlements(mixed $value): array
    {
        if (is_string($value)) {
            $value = preg_split('/[\r\n,]+/', $value) ?: [];
        }

        if (! is_array($value)) {
            return [];
        }

        $entitlements = [];

        foreach ($value as $key => $enabled) {
            if (is_int($key)) {
                $key = (string) $enabled;
                $enabled = true;
            }

            $key = str((string) $key)
                ->trim()
                ->lower()
                ->replace(['-', ' '], '_')
                ->toString();

            if ($key === '') {
                continue;
            }

            $entitlements[$key] = filter_var($enabled, FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE) ?? (bool) $enabled;
        }

        ksort($entitlements);

        return $entitlements;
    }

    private function assertNotExpired(array $payload): void
    {
        if (($payload['type'] ?? null) === 'lifetime' || blank($payload['expires_at'] ?? null)) {
            return;
        }

        if ($this->dateValue((string) $payload['expires_at'])->endOfDay()->isPast()) {
            throw new RuntimeException('The signed license key has expired.');
        }
    }

    private function normalizeDomain(string $domain): string
    {
        return str($domain)
            ->trim()
            ->lower()
            ->replace(['https://', 'http://'], '')
            ->before('/')
            ->toString();
    }

    private function date(string $value, string $field): string
    {
        $date = CarbonImmutable::createFromFormat('!Y-m-d', $value);

        if (! $date || $date->format('Y-m-d') !== $value) {
            throw new RuntimeException("The {$field} date must use YYYY-MM-DD format.");
        }

        return $date->format('Y-m-d');
    }

    private function dateValue(string $value): CarbonImmutable
    {
        return CarbonImmutable::createFromFormat('!Y-m-d', $value);
    }

    private function signature(string $encodedPayload): string
    {
        return hash_hmac('sha256', $encodedPayload, $this->signingKey());
    }

    private function signingKey(): string
    {
        $key = trim((string) config('licensing.signing_key'));

        if ($key === '') {
            throw new RuntimeException('License signing key is not configured. Set SANFAANI_LICENSE_SIGNING_KEY in the seller environment.');
        }

        return $key;
    }

    private function json(array $payload): string
    {
        $json = json_encode($payload, JSON_UNESCAPED_SLASHES);

        if (! is_string($json)) {
            throw new RuntimeException('Unable to encode the license payload.');
        }

        return $json;
    }

    private function base32Encode(string $value): string
    {
        $bits = '';

        foreach (str_split($value) as $byte) {
            $bits .= str_pad(decbin(ord($byte)), 8, '0', STR_PAD_LEFT);
        }

        $encoded = '';

        foreach (str_split($bits, 5) as $chunk) {
            $encoded .= self::ALPHABET[bindec(str_pad($chunk, 5, '0', STR_PAD_RIGHT))];
        }

        return $encoded;
    }

    private function base32Decode(string $value): string
    {
        $lookup = array_flip(str_split(self::ALPHABET));
        $bits = '';

        foreach (str_split($value) as $char) {
            if (! array_key_exists($char, $lookup)) {
                throw new RuntimeException('The signed license payload is not valid base32.');
            }

            $bits .= str_pad(decbin($lookup[$char]), 5, '0', STR_PAD_LEFT);
        }

        $decoded = '';

        foreach (str_split($bits, 8) as $byte) {
            if (strlen($byte) < 8) {
                continue;
            }

            $decoded .= chr(bindec($byte));
        }

        return $decoded;
    }
}
