<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->repairUsersTable();
        $this->repairMailSettingsTable();
    }

    public function down(): void
    {
        // Intentionally non-destructive: this repair migration protects existing data.
    }

    private function repairUsersTable(): void
    {
        if (! Schema::hasTable('users')) {
            return;
        }

        if (! Schema::hasColumn('users', 'staff_code')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('staff_code', 80)->nullable()->after('email');
            });
        }

        if (! Schema::hasColumn('users', 'must_change_password')) {
            Schema::table('users', function (Blueprint $table) {
                $table->boolean('must_change_password')->default(false)->after('password');
            });
        }

        if (! $this->hasIndex('users', 'users_staff_code_unique')) {
            Schema::table('users', function (Blueprint $table) {
                if ($this->staffCodeHasDuplicates()) {
                    if (! $this->hasIndex('users', 'users_staff_code_index')) {
                        $table->index('staff_code', 'users_staff_code_index');
                    }

                    return;
                }

                $table->unique('staff_code', 'users_staff_code_unique');
            });
        }
    }

    private function repairMailSettingsTable(): void
    {
        if (! Schema::hasTable('mail_settings')) {
            return;
        }

        if (! Schema::hasColumn('mail_settings', 'school_id')) {
            Schema::table('mail_settings', function (Blueprint $table) {
                if (Schema::hasTable('schools')) {
                    $table->foreignId('school_id')->nullable()->after('id')->constrained()->nullOnDelete();

                    return;
                }

                $table->unsignedBigInteger('school_id')->nullable()->after('id');
            });
        }

        if (! Schema::hasColumn('mail_settings', 'reply_to_email')) {
            Schema::table('mail_settings', function (Blueprint $table) {
                $table->string('reply_to_email')->nullable()->after('from_name');
            });
        }

        if (
            Schema::hasColumn('mail_settings', 'school_id')
            && Schema::hasColumn('mail_settings', 'is_enabled')
            && ! $this->hasIndex('mail_settings', 'mail_settings_school_id_is_enabled_index')
        ) {
            Schema::table('mail_settings', function (Blueprint $table) {
                $table->index(['school_id', 'is_enabled'], 'mail_settings_school_id_is_enabled_index');
            });
        }
    }

    private function staffCodeHasDuplicates(): bool
    {
        return DB::table('users')
            ->select('staff_code')
            ->whereNotNull('staff_code')
            ->groupBy('staff_code')
            ->havingRaw('COUNT(*) > 1')
            ->exists();
    }

    private function hasIndex(string $table, string $index): bool
    {
        try {
            return Schema::hasIndex($table, $index);
        } catch (Throwable) {
            return false;
        }
    }
};
