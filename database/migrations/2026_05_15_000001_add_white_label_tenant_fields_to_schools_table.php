<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('schools', function (Blueprint $table) {
            if (! Schema::hasColumn('schools', 'smtp_host')) {
                $table->string('smtp_host')->nullable()->after('subscription_status');
            }

            if (! Schema::hasColumn('schools', 'smtp_port')) {
                $table->string('smtp_port')->nullable()->after('smtp_host');
            }

            if (! Schema::hasColumn('schools', 'smtp_username')) {
                $table->string('smtp_username')->nullable()->after('smtp_port');
            }

            if (! Schema::hasColumn('schools', 'smtp_password')) {
                $table->text('smtp_password')->nullable()->after('smtp_username');
            }

            if (! Schema::hasColumn('schools', 'smtp_encryption')) {
                $table->string('smtp_encryption')->nullable()->default('tls')->after('smtp_password');
            }

            if (! Schema::hasColumn('schools', 'sender_email')) {
                $table->string('sender_email')->nullable()->after('smtp_encryption');
            }

            if (! Schema::hasColumn('schools', 'sender_name')) {
                $table->string('sender_name')->nullable()->after('sender_email');
            }

            if (! Schema::hasColumn('schools', 'logo_path')) {
                $table->string('logo_path')->nullable()->after('logo');
            }

            if (! Schema::hasColumn('schools', 'primary_color')) {
                $table->string('primary_color')->nullable()->default('#4f46e5')->after('logo_path');
            }

            if (! Schema::hasColumn('schools', 'result_checker_slug')) {
                $table->string('result_checker_slug')->nullable()->after('primary_color');
            }

            if (! Schema::hasColumn('schools', 'is_result_checker_enabled')) {
                $table->boolean('is_result_checker_enabled')->default(false)->after('result_checker_slug');
            }

            if (! Schema::hasColumn('schools', 'custom_css')) {
                $table->text('custom_css')->nullable()->after('is_result_checker_enabled');
            }
        });

        if (! $this->indexExists('schools', 'schools_result_checker_slug_unique')) {
            Schema::table('schools', function (Blueprint $table) {
                $table->unique('result_checker_slug', 'schools_result_checker_slug_unique');
            });
        }
    }

    public function down(): void
    {
        if ($this->indexExists('schools', 'schools_result_checker_slug_unique')) {
            Schema::table('schools', function (Blueprint $table) {
                $table->dropUnique('schools_result_checker_slug_unique');
            });
        }

        Schema::table('schools', function (Blueprint $table) {
            $columns = [
                'smtp_host',
                'smtp_port',
                'smtp_username',
                'smtp_password',
                'smtp_encryption',
                'sender_email',
                'sender_name',
                'logo_path',
                'primary_color',
                'result_checker_slug',
                'is_result_checker_enabled',
                'custom_css',
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('schools', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }

    private function indexExists(string $table, string $indexName): bool
    {
        $driver = DB::connection()->getDriverName();

        if ($driver === 'sqlite') {
            return collect(DB::select("PRAGMA index_list({$table})"))
                ->contains(fn ($index) => $index->name === $indexName);
        }

        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            $result = DB::selectOne(
                'SELECT COUNT(*) AS aggregate FROM information_schema.statistics WHERE table_schema = DATABASE() AND table_name = ? AND index_name = ?',
                [$table, $indexName]
            );

            return ((int) ($result->aggregate ?? 0)) > 0;
        }

        if ($driver === 'pgsql') {
            $result = DB::selectOne(
                'SELECT COUNT(*) AS aggregate FROM pg_indexes WHERE tablename = ? AND indexname = ?',
                [$table, $indexName]
            );

            return ((int) ($result->aggregate ?? 0)) > 0;
        }

        return false;
    }
};
