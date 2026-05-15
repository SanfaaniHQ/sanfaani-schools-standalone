<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('schools')) {
            Schema::table('schools', function (Blueprint $table) {
                if (! Schema::hasColumn('schools', 'secondary_color')) {
                    $table->string('secondary_color')->nullable()->default('#0f766e')->after('primary_color');
                }

                if (! Schema::hasColumn('schools', 'favicon_path')) {
                    $table->string('favicon_path')->nullable()->after('logo_path');
                }

                if (! Schema::hasColumn('schools', 'login_background_path')) {
                    $table->string('login_background_path')->nullable()->after('favicon_path');
                }

                if (! Schema::hasColumn('schools', 'report_header_path')) {
                    $table->string('report_header_path')->nullable()->after('login_background_path');
                }

                if (! Schema::hasColumn('schools', 'email_logo_path')) {
                    $table->string('email_logo_path')->nullable()->after('report_header_path');
                }

                if (! Schema::hasColumn('schools', 'school_motto')) {
                    $table->string('school_motto')->nullable()->after('email_logo_path');
                }
            });
        }

        if (Schema::hasTable('scratch_card_batches')) {
            Schema::table('scratch_card_batches', function (Blueprint $table) {
                if (! Schema::hasColumn('scratch_card_batches', 'batch_code')) {
                    $table->string('batch_code', 80)->nullable()->after('id');
                }

                if (! Schema::hasColumn('scratch_card_batches', 'requested_by')) {
                    $table->foreignId('requested_by')->nullable()->after('school_id')->constrained('users')->nullOnDelete();
                }

                if (! Schema::hasColumn('scratch_card_batches', 'approved_at')) {
                    $table->timestamp('approved_at')->nullable()->after('payment_confirmed_by');
                }

                if (! Schema::hasColumn('scratch_card_batches', 'approved_by')) {
                    $table->foreignId('approved_by')->nullable()->after('approved_at')->constrained('users')->nullOnDelete();
                }

                if (! Schema::hasColumn('scratch_card_batches', 'rejected_at')) {
                    $table->timestamp('rejected_at')->nullable()->after('approved_by');
                }

                if (! Schema::hasColumn('scratch_card_batches', 'rejected_by')) {
                    $table->foreignId('rejected_by')->nullable()->after('rejected_at')->constrained('users')->nullOnDelete();
                }

                if (! Schema::hasColumn('scratch_card_batches', 'approval_note')) {
                    $table->text('approval_note')->nullable()->after('rejected_by');
                }

                if (! Schema::hasColumn('scratch_card_batches', 'failed_generation_at')) {
                    $table->timestamp('failed_generation_at')->nullable()->after('generated_by');
                }

                if (! Schema::hasColumn('scratch_card_batches', 'failed_generation_reason')) {
                    $table->text('failed_generation_reason')->nullable()->after('failed_generation_at');
                }

                if (! Schema::hasColumn('scratch_card_batches', 'last_exported_at')) {
                    $table->timestamp('last_exported_at')->nullable()->after('failed_generation_reason');
                }

                if (! Schema::hasColumn('scratch_card_batches', 'last_exported_by')) {
                    $table->foreignId('last_exported_by')->nullable()->after('last_exported_at')->constrained('users')->nullOnDelete();
                }
            });

            if (! $this->indexExists('scratch_card_batches', 'scratch_card_batches_batch_code_unique')) {
                Schema::table('scratch_card_batches', function (Blueprint $table) {
                    $table->unique('batch_code', 'scratch_card_batches_batch_code_unique');
                });
            }

            if (! $this->indexExists('scratch_card_batches', 'scratch_batches_school_status_payment_idx')) {
                Schema::table('scratch_card_batches', function (Blueprint $table) {
                    $table->index(['school_id', 'status', 'payment_status', 'created_at'], 'scratch_batches_school_status_payment_idx');
                });
            }

            if (! $this->indexExists('scratch_card_batches', 'scratch_batches_approval_idx')) {
                Schema::table('scratch_card_batches', function (Blueprint $table) {
                    $table->index(['approved_at', 'rejected_at', 'created_at'], 'scratch_batches_approval_idx');
                });
            }
        }

        if (Schema::hasTable('scratch_cards')) {
            if (! $this->indexExists('scratch_cards', 'scratch_cards_school_status_expiry_idx')) {
                Schema::table('scratch_cards', function (Blueprint $table) {
                    $table->index(['school_id', 'status', 'expires_at'], 'scratch_cards_school_status_expiry_idx');
                });
            }
        }
    }

    public function down(): void
    {
        // Zero-data-loss rollback policy: keep enterprise branding and scratch-card audit data intact.
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
