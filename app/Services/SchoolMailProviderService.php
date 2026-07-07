<?php

namespace App\Services;

use App\Exceptions\MailConfigurationException;
use App\Models\School;
use App\Models\SchoolMailProviderProfile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Throwable;

class SchoolMailProviderService
{
    public const TYPES = [
        'gmail' => 'Gmail',
        'google_workspace' => 'Google Workspace',
        'cpanel' => 'cPanel / Webmail',
        'custom_smtp' => 'Custom SMTP',
    ];

    public function tableIsReady(): bool
    {
        try {
            return Schema::hasTable('school_mail_provider_profiles');
        } catch (Throwable) {
            return false;
        }
    }

    public function forSchool(School|int $school, bool $enabledOnly = false): Collection
    {
        if (! $this->tableIsReady()) {
            return collect();
        }

        $schoolId = $school instanceof School ? $school->id : $school;

        return SchoolMailProviderProfile::query()
            ->where('school_id', $schoolId)
            ->when($enabledOnly, fn ($query) => $query->where('is_enabled', true))
            ->orderByDesc('is_primary')
            ->orderBy('priority')
            ->orderBy('id')
            ->get();
    }

    public function enabledChain(School|int $school): Collection
    {
        return $this->forSchool($school, true);
    }

    public function primaryForSchool(School|int $school): ?SchoolMailProviderProfile
    {
        return $this->enabledChain($school)->first();
    }

    public function save(School $school, array $data, ?SchoolMailProviderProfile $profile = null): SchoolMailProviderProfile
    {
        if ($profile && (int) $profile->school_id !== (int) $school->id) {
            abort(403);
        }

        return DB::transaction(function () use ($school, $data, $profile): SchoolMailProviderProfile {
            SchoolMailProviderProfile::where('school_id', $school->id)->lockForUpdate()->get();
            $profile ??= new SchoolMailProviderProfile(['school_id' => $school->id]);
            $isFirst = ! SchoolMailProviderProfile::where('school_id', $school->id)->exists();
            $normalized = $this->normalizedUpdateData($data, $profile);
            $makePrimary = (bool) ($normalized['is_primary'] ?? false) || $isFirst;

            if ($makePrimary) {
                SchoolMailProviderProfile::where('school_id', $school->id)->update(['is_primary' => false]);
                $normalized['is_primary'] = true;
                $normalized['is_enabled'] = true;
            }

            $profile->fill($normalized);
            $profile->school_id = $school->id;
            $profile->save();

            if (! SchoolMailProviderProfile::where('school_id', $school->id)
                ->where('is_enabled', true)
                ->where('is_primary', true)
                ->exists()) {
                $replacement = SchoolMailProviderProfile::where('school_id', $school->id)
                    ->where('is_enabled', true)
                    ->orderBy('priority')
                    ->orderBy('id')
                    ->first();
                $replacement?->update(['is_primary' => true]);
            }

            app(SchoolSmtpService::class)->forgetRuntimeMailers();

            return $profile->fresh() ?? $profile;
        });
    }

    public function makePrimary(School $school, SchoolMailProviderProfile $profile): SchoolMailProviderProfile
    {
        abort_unless((int) $profile->school_id === (int) $school->id, 403);

        return DB::transaction(function () use ($school, $profile): SchoolMailProviderProfile {
            SchoolMailProviderProfile::where('school_id', $school->id)->lockForUpdate()->get();
            SchoolMailProviderProfile::where('school_id', $school->id)->update(['is_primary' => false]);
            $profile->update(['is_primary' => true, 'is_enabled' => true]);
            app(SchoolSmtpService::class)->forgetRuntimeMailers();

            return $profile->fresh();
        });
    }

    public function toggle(School $school, SchoolMailProviderProfile $profile): SchoolMailProviderProfile
    {
        abort_unless((int) $profile->school_id === (int) $school->id, 403);

        return DB::transaction(function () use ($school, $profile): SchoolMailProviderProfile {
            $wasPrimary = $profile->is_primary;
            $profile->update([
                'is_enabled' => ! $profile->is_enabled,
                'is_primary' => $profile->is_enabled && $profile->is_primary ? false : $profile->is_primary,
            ]);

            if ($wasPrimary && ! $profile->is_enabled) {
                SchoolMailProviderProfile::where('school_id', $school->id)
                    ->where('is_enabled', true)
                    ->whereKeyNot($profile->id)
                    ->orderBy('priority')
                    ->orderBy('id')
                    ->first()?->update(['is_primary' => true]);
            }

            app(SchoolSmtpService::class)->forgetRuntimeMailers();

            return $profile->fresh();
        });
    }

    public function move(School $school, SchoolMailProviderProfile $profile, string $direction): void
    {
        abort_unless((int) $profile->school_id === (int) $school->id, 403);

        $operator = $direction === 'up' ? '<' : '>';
        $order = $direction === 'up' ? 'desc' : 'asc';
        $other = SchoolMailProviderProfile::where('school_id', $school->id)
            ->where('is_primary', false)
            ->whereKeyNot($profile->id)
            ->where('priority', $operator, $profile->priority)
            ->orderBy('priority', $order)
            ->orderBy('id', $order)
            ->first();

        if (! $other) {
            return;
        }

        DB::transaction(function () use ($profile, $other): void {
            $priority = $profile->priority;
            $profile->update(['priority' => $other->priority]);
            $other->update(['priority' => $priority]);
        });
    }

    public function delete(School $school, SchoolMailProviderProfile $profile): void
    {
        abort_unless((int) $profile->school_id === (int) $school->id, 403);

        DB::transaction(function () use ($school, $profile): void {
            $wasPrimary = $profile->is_primary;
            $profile->delete();

            if ($wasPrimary) {
                SchoolMailProviderProfile::where('school_id', $school->id)
                    ->where('is_enabled', true)
                    ->orderBy('priority')
                    ->orderBy('id')
                    ->first()?->update(['is_primary' => true]);
            }
        });
    }

    public function normalize(SchoolMailProviderProfile $profile, ?string $passwordOverride = null): array
    {
        $state = $this->passwordState($profile);
        $password = filled($passwordOverride) ? $passwordOverride : $state['password'];

        if (! filled($passwordOverride) && $state['unusable']) {
            throw new MailConfigurationException(
                'password_decryption_failed',
                'The saved SMTP password cannot be decrypted. Re-enter and save the password.'
            );
        }

        $normalized = app(SchoolSmtpService::class)->normalize([
            'is_enabled' => $profile->is_enabled,
            'mailer' => $profile->mailer,
            'host' => $profile->host,
            'port' => $profile->port,
            'username' => $profile->username,
            'password' => $password,
            'encryption' => $profile->encryption,
            'timeout' => $profile->timeout,
            'from_address' => $profile->from_address,
            'from_name' => $profile->from_name,
            'reply_to_address' => $profile->reply_to_address,
            'reply_to_name' => $profile->reply_to_name,
        ], $profile->school);

        app(SchoolSmtpService::class)->assertSmtpReady($normalized);

        return $normalized;
    }

    public function passwordState(SchoolMailProviderProfile $profile): array
    {
        $raw = $profile->getAttributes()['password'] ?? $profile->getRawOriginal('password');

        if (! filled($raw)) {
            return ['available' => false, 'unusable' => false, 'password' => null];
        }

        try {
            $password = $profile->password;

            if (! filled($password)) {
                return ['available' => false, 'unusable' => true, 'password' => null];
            }

            return ['available' => true, 'unusable' => false, 'password' => (string) $password];
        } catch (Throwable) {
            return ['available' => false, 'unusable' => true, 'password' => null];
        }
    }

    public function isComplete(SchoolMailProviderProfile $profile): bool
    {
        $password = $this->passwordState($profile);

        return filled($profile->host)
            && filled($profile->port)
            && filled($profile->from_address)
            && (! filled($profile->username) || $password['available']);
    }

    public function normalizedUpdateData(array $data, ?SchoolMailProviderProfile $profile = null): array
    {
        $data['mailer'] = 'smtp';
        $data['provider_type'] = array_key_exists($data['provider_type'] ?? '', self::TYPES)
            ? $data['provider_type']
            : 'custom_smtp';
        $data['is_enabled'] = (bool) ($data['is_enabled'] ?? false);
        $data['is_primary'] = (bool) ($data['is_primary'] ?? false);
        $data['priority'] = max(1, (int) ($data['priority'] ?? 100));
        $data['timeout'] = max(1, min(120, (int) ($data['timeout'] ?? 10)));

        if (! filled($data['password'] ?? null) || $this->isPasswordMask($data['password'] ?? null)) {
            unset($data['password']);
        }

        if (in_array($data['provider_type'], ['gmail', 'google_workspace'], true)) {
            $data['host'] = 'smtp.gmail.com';
        }

        return $data;
    }

    private function isPasswordMask(mixed $value): bool
    {
        return is_string($value) && preg_match('/^\*{6,}$/', trim($value)) === 1;
    }
}
