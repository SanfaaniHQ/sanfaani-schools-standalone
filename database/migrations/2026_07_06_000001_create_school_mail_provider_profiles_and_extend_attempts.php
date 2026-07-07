<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
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
                $table->timestamp('last_tested_at')->nullable();
                $table->string('last_error_category', 80)->nullable();
                $table->json('metadata')->nullable();
                $table->timestamps();

                $table->index(['school_id', 'is_enabled', 'is_primary'], 'school_mail_profile_active_idx');
                $table->index(['school_id', 'priority'], 'school_mail_profile_priority_idx');
            });
        }

        if (Schema::hasTable('mail_delivery_attempts')) {
            Schema::table('mail_delivery_attempts', function (Blueprint $table) {
                if (! Schema::hasColumn('mail_delivery_attempts', 'provider_profile_id')) {
                    $table->foreignId('provider_profile_id')->nullable()->after('initiating_user_id')
                        ->constrained('school_mail_provider_profiles')->nullOnDelete();
                }
                if (! Schema::hasColumn('mail_delivery_attempts', 'provider_name')) {
                    $table->string('provider_name', 160)->nullable()->after('provider_profile_id');
                }
                if (! Schema::hasColumn('mail_delivery_attempts', 'provider_type')) {
                    $table->string('provider_type', 40)->nullable()->after('provider_name');
                }
                if (! Schema::hasColumn('mail_delivery_attempts', 'provider_position')) {
                    $table->string('provider_position', 20)->nullable()->after('provider_type');
                }
                if (! Schema::hasColumn('mail_delivery_attempts', 'attempt_sequence')) {
                    $table->unsignedInteger('attempt_sequence')->nullable()->after('provider_position');
                }
                if (! Schema::hasColumn('mail_delivery_attempts', 'message_kind')) {
                    $table->string('message_kind', 20)->default('transactional')->after('configuration');
                }
            });
        }

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
                if (DB::table('school_mail_provider_profiles')->where('school_id', $setting->school_id)->exists()) {
                    return;
                }

                $host = strtolower((string) ($setting->host ?? ''));
                $type = $host === 'smtp.gmail.com' ? 'gmail' : 'custom_smtp';
                $metadata = json_decode((string) ($setting->metadata ?? ''), true);

                DB::table('school_mail_provider_profiles')->insert([
                    'school_id' => $setting->school_id,
                    'name' => $type === 'gmail' ? 'School Gmail' : 'Legacy School SMTP',
                    'provider_type' => $type,
                    'mailer' => 'smtp',
                    'host' => $setting->host ?: 'localhost',
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
                    'is_primary' => true,
                    'priority' => 10,
                    'last_test_status' => data_get($metadata, 'last_test.outcome'),
                    'last_tested_at' => data_get($metadata, 'last_test.at'),
                    'last_error_category' => data_get($metadata, 'last_test.category'),
                    'metadata' => json_encode(['migrated_from_mail_setting_id' => $setting->id]),
                    'created_at' => $setting->created_at ?? now(),
                    'updated_at' => $setting->updated_at ?? now(),
                ]);
            });
    }
};
