<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('mail_settings', 'school_id')) {
            Schema::table('mail_settings', function (Blueprint $table) {
                $table->foreignId('school_id')->nullable()->after('id')->constrained()->nullOnDelete();
            });
        }

        if (! Schema::hasColumn('mail_settings', 'reply_to_email')) {
            Schema::table('mail_settings', function (Blueprint $table) {
                $table->string('reply_to_email')->nullable()->after('from_name');
            });
        }

        if (! $this->indexExists('mail_settings', 'mail_settings_school_id_is_enabled_index')) {
            Schema::table('mail_settings', function (Blueprint $table) {
                $table->index(['school_id', 'is_enabled']);
            });
        }
    }

    public function down(): void
    {
        if ($this->indexExists('mail_settings', 'mail_settings_school_id_is_enabled_index')) {
            Schema::table('mail_settings', function (Blueprint $table) {
                $table->dropIndex('mail_settings_school_id_is_enabled_index');
            });
        }

        if (Schema::hasColumn('mail_settings', 'school_id')) {
            Schema::table('mail_settings', function (Blueprint $table) {
                $table->dropConstrainedForeignId('school_id');
            });
        }

        if (Schema::hasColumn('mail_settings', 'reply_to_email')) {
            Schema::table('mail_settings', function (Blueprint $table) {
                $table->dropColumn('reply_to_email');
            });
        }
    }

    private function indexExists(string $table, string $indexName): bool
    {
        $driver = DB::connection()->getDriverName();

        // SQLite: Use Laravel's Schema facade which handles SQLite introspection
        if ($driver === 'sqlite') {
            $indexes = DB::select("PRAGMA index_list({$table})");

            return collect($indexes)->contains(fn ($index) => $index->name === $indexName);
        }

        // MySQL/MariaDB: Use information_schema
        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            $result = DB::selectOne(
                'SELECT COUNT(*) AS aggregate FROM information_schema.statistics WHERE table_schema = DATABASE() AND table_name = ? AND index_name = ?',
                [$table, $indexName]
            );

            return ((int) ($result->aggregate ?? 0)) > 0;
        }

        // PostgreSQL: Use pg_indexes
        if ($driver === 'pgsql') {
            $result = DB::selectOne(
                'SELECT COUNT(*) AS aggregate FROM pg_indexes WHERE tablename = ? AND indexname = ?',
                [$table, $indexName]
            );

            return ((int) ($result->aggregate ?? 0)) > 0;
        }

        // Fallback: assume index doesn't exist (safe default - will attempt to create)
        return false;
    }
};
