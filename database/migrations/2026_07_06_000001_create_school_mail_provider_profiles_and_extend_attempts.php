<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('school_mail_provider_profiles')) {
            Schema::create('school_mail_provider_profiles', function (Blueprint $table) {
                $table->id();
                $table->foreignId('school_id')->constrained()->cascadeOnDelete();
                $table->string('name', 160);
                $table->string('provider_type', 40)->default('custom_smtp');
                $table->string('mailer', 40)->default('smtp');
                $table->string('host');
                $table->unsignedInteger('port');
                $table->string('username')->nullable();
                $table->text('password')->nullable();
                $table->string('encryption', 20)->default('tls');
                $table->string('from_address');
                $table->string('from_name', 160)->nullable();
                $table->string('reply_to_address')->nullable();
                $table->string('reply_to_name', 160)->nullable();
                $table->unsignedInteger('timeout')->default(10);
                $table->boolean('is_enabled')->default(true);
                $table->boolean('is_primary')->default(false);
                $table->unsignedInteger('priority')->default(100);
                $table->string('last_test_status', 50)->nullable();
                $table->dateTime('last_tested_at')->nullable();
                $table->string('last_error_category', 80)->nullable();
                $table->json('metadata')->nullable();
                $table->timestamps();

                $table->index(['school_id', 'is_enabled', 'is_primary'], 'school_mail_profile_active_idx');
                $table->index(['school_id', 'priority'], 'school_mail_profile_priority_idx');
            });
        }

        $this->ensureMailDeliveryAttemptColumns();

        $this->backfillLegacySchoolSettings();
    }

    public function down(): void
    {
        if (Schema::hasTable('mail_delivery_attempts')) {
            Schema::table('mail_delivery_attempts', function (Blueprint $table) {
                foreach (['provider_profile_id', 'provider_name', 'provider_type', 'provider_position', 'attempt_sequence', 'message_kind'] as $column) {
                    if (Schema::hasColumn('mail_delivery_attempts', $column)) {
                        if ($column === 'provider_profile_id') {
                            $table->dropConstrainedForeignId($column);
                        } else {
                            $table->dropColumn($column);
                        }
                    }
                }
            });
        }

        Schema::dropIfExists('school_mail_provider_profiles');
    }

    private function backfillLegacySchoolSettings(): void
    {
        if (! Schema::hasTable('mail_settings')
            || ! Schema::hasColumn('mail_settings', 'school_id')
            || ! Schema::hasTable('school_mail_provider_profiles')) {
            return;
        }

        DB::table('mail_settings')
            ->whereNotNull('school_id')
            ->where('mailer', 'smtp')
            ->orderBy('id')
            ->each(function (object $setting): void {
                $metadata = $this->metadata($setting->metadata ?? null);
                $attributes = $this->legacyProfileAttributes($setting, $metadata);

                $existing = $this->matchingMigratedProvider($setting, $attributes);

                if ($existing) {
                    $this->updateMissingLegacyProfileFields($existing, $attributes);

                    return;
                }

                DB::table('school_mail_provider_profiles')->insert($attributes);
            });
    }

    private function ensureMailDeliveryAttemptColumns(): void
    {
        if (! Schema::hasTable('mail_delivery_attempts')) {
            return;
        }

        $this->addColumnIfMissing('mail_delivery_attempts', 'provider_profile_id', function (Blueprint $table): void {
            $table->foreignId('provider_profile_id')->nullable()
                ->constrained('school_mail_provider_profiles')->nullOnDelete();
        });
        $this->addColumnIfMissing('mail_delivery_attempts', 'provider_name', function (Blueprint $table): void {
            $table->string('provider_name', 160)->nullable();
        });
        $this->addColumnIfMissing('mail_delivery_attempts', 'provider_type', function (Blueprint $table): void {
            $table->string('provider_type', 40)->nullable();
        });
        $this->addColumnIfMissing('mail_delivery_attempts', 'provider_position', function (Blueprint $table): void {
            $table->string('provider_position', 20)->nullable();
        });
        $this->addColumnIfMissing('mail_delivery_attempts', 'attempt_sequence', function (Blueprint $table): void {
            $table->unsignedInteger('attempt_sequence')->nullable();
        });
        $this->addColumnIfMissing('mail_delivery_attempts', 'message_kind', function (Blueprint $table): void {
            $table->string('message_kind', 20)->default('transactional');
        });
    }

    private function addColumnIfMissing(string $tableName, string $columnName, callable $definition): void
    {
        if (Schema::hasColumn($tableName, $columnName)) {
            return;
        }

        Schema::table($tableName, function (Blueprint $table) use ($definition): void {
            $definition($table);
        });
    }

    private function legacyProfileAttributes(object $setting, array $metadata): array
    {
        $host = (string) ($setting->host ?: 'localhost');
        $type = strtolower($host) === 'smtp.gmail.com' ? 'gmail' : 'custom_smtp';
        $now = $this->mysqlDateTime(now());
        $hasPrimary = DB::table('school_mail_provider_profiles')
            ->where('school_id', $setting->school_id)
            ->where('is_primary', true)
            ->exists();

        return [
            'school_id' => $setting->school_id,
            'name' => $type === 'gmail' ? 'School Gmail' : 'Legacy School SMTP',
            'provider_type' => $type,
            'mailer' => 'smtp',
            'host' => $host,
            'port' => $setting->port ?: 587,
            'username' => $setting->username,
            // Copy the existing encrypted payload without decrypting or re-encrypting it.
            'password' => $setting->password,
            'encryption' => $setting->encryption ?: 'tls',
            'from_address' => $setting->from_address ?: ($setting->username ?: 'mailer@localhost'),
            'from_name' => $setting->from_name,
            'reply_to_address' => $setting->reply_to_email ?? null,
            'reply_to_name' => null,
            'timeout' => max(1, min(120, (int) data_get($metadata, 'timeout', 10))),
            'is_enabled' => (bool) $setting->is_enabled,
            'is_primary' => ! $hasPrimary,
            'priority' => 10,
            'last_test_status' => data_get($metadata, 'last_test.outcome'),
            'last_tested_at' => $this->mysqlDateTime(data_get($metadata, 'last_test.at')),
            'last_error_category' => data_get($metadata, 'last_test.category'),
            'metadata' => json_encode(['migrated_from_mail_setting_id' => $setting->id]),
            'created_at' => $this->mysqlDateTime($setting->created_at ?? null) ?? $now,
            'updated_at' => $this->mysqlDateTime($setting->updated_at ?? null) ?? $now,
        ];
    }

    private function matchingMigratedProvider(object $setting, array $attributes): ?object
    {
        return DB::table('school_mail_provider_profiles')
            ->where('school_id', $setting->school_id)
            ->where('provider_type', $attributes['provider_type'])
            ->orderBy('id')
            ->get()
            ->first(function (object $profile) use ($setting, $attributes): bool {
                $metadata = $this->metadata($profile->metadata ?? null);

                if ((string) data_get($metadata, 'migrated_from_mail_setting_id') === (string) $setting->id) {
                    return true;
                }

                return $this->sameProviderValue($profile->host ?? null, $attributes['host'], false)
                    && $this->sameProviderValue($profile->username ?? null, $attributes['username'])
                    && $this->sameProviderValue($profile->from_address ?? null, $attributes['from_address']);
            });
    }

    private function updateMissingLegacyProfileFields(object $profile, array $attributes): void
    {
        $updates = [];

        foreach ([
            'name',
            'mailer',
            'host',
            'port',
            'username',
            'encryption',
            'from_address',
            'from_name',
            'reply_to_address',
            'reply_to_name',
            'timeout',
            'last_test_status',
            'last_tested_at',
            'last_error_category',
        ] as $field) {
            if (blank($profile->{$field} ?? null) && filled($attributes[$field] ?? null)) {
                $updates[$field] = $attributes[$field];
            }
        }

        if (blank($profile->password ?? null) && filled($attributes['password'] ?? null)) {
            $updates['password'] = $attributes['password'];
        }

        $metadata = $this->metadata($profile->metadata ?? null);
        $migratedFrom = data_get($this->metadata($attributes['metadata'] ?? null), 'migrated_from_mail_setting_id');

        if ((string) data_get($metadata, 'migrated_from_mail_setting_id') !== (string) $migratedFrom) {
            $metadata['migrated_from_mail_setting_id'] = $migratedFrom;
            $updates['metadata'] = json_encode($metadata);
        }

        if ($updates === []) {
            return;
        }

        $updates['updated_at'] = $this->mysqlDateTime(now());

        DB::table('school_mail_provider_profiles')
            ->where('id', $profile->id)
            ->update($updates);
    }

    private function metadata(mixed $value): array
    {
        if (is_array($value)) {
            return $value;
        }

        $metadata = json_decode((string) ($value ?? ''), true);

        return is_array($metadata) ? $metadata : [];
    }

    private function sameProviderValue(mixed $left, mixed $right, bool $caseSensitive = true): bool
    {
        $left = trim((string) $left);
        $right = trim((string) $right);

        if (! $caseSensitive) {
            $left = strtolower($left);
            $right = strtolower($right);
        }

        return $left === $right;
    }

    private function mysqlDateTime($value): ?string
    {
        if (blank($value)) {
            return null;
        }

        try {
            return Carbon::parse($value)->format('Y-m-d H:i:s');
        } catch (Throwable) {
            return null;
        }
    }
};
