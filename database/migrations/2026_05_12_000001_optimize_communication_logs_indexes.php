<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const TABLE = 'communication_logs';

    public function up(): void
    {
        if (! Schema::hasTable(self::TABLE)) {
            return;
        }

        Schema::table(self::TABLE, function (Blueprint $table) {
            $this->dropIndexIfExists($table, 'communication_logs_school_id_status_index');
            $this->dropIndexIfExists($table, 'communication_logs_type_status_index');
            $this->dropIndexIfExists($table, 'communication_logs_sent_at_index');

            $this->addIndexIfMissing($table, ['school_id', 'status', 'created_at'], 'comm_logs_school_status_created_idx');
            $this->addIndexIfMissing($table, ['school_id', 'type', 'created_at'], 'comm_logs_school_type_created_idx');
            $this->addIndexIfMissing($table, ['sender_id', 'created_at'], 'comm_logs_sender_created_idx');
            $this->addIndexIfMissing($table, ['status', 'created_at'], 'comm_logs_status_created_idx');
            $this->addIndexIfMissing($table, 'sent_at', 'comm_logs_sent_at_idx');
        });

        $this->addRecipientSearchIndex();
    }

    public function down(): void
    {
        if (! Schema::hasTable(self::TABLE)) {
            return;
        }

        Schema::table(self::TABLE, function (Blueprint $table) {
            $this->dropIndexIfExists($table, 'comm_logs_recipient_idx');
            $this->dropIndexIfExists($table, 'comm_logs_school_status_created_idx');
            $this->dropIndexIfExists($table, 'comm_logs_school_type_created_idx');
            $this->dropIndexIfExists($table, 'comm_logs_sender_created_idx');
            $this->dropIndexIfExists($table, 'comm_logs_status_created_idx');
            $this->dropIndexIfExists($table, 'comm_logs_sent_at_idx');

            $this->addIndexIfMissing($table, ['school_id', 'status'], 'communication_logs_school_id_status_index');
            $this->addIndexIfMissing($table, ['type', 'status'], 'communication_logs_type_status_index');
            $this->addIndexIfMissing($table, 'sent_at', 'communication_logs_sent_at_index');
        });
    }

    private function addIndexIfMissing(Blueprint $table, array|string $columns, string $indexName): void
    {
        if (! Schema::hasIndex(self::TABLE, $indexName)) {
            $table->index($columns, $indexName);
        }
    }

    private function dropIndexIfExists(Blueprint $table, string $indexName): void
    {
        if (Schema::hasIndex(self::TABLE, $indexName)) {
            $table->dropIndex($indexName);
        }
    }

    private function addRecipientSearchIndex(): void
    {
        if (Schema::hasIndex(self::TABLE, 'comm_logs_recipient_idx')) {
            return;
        }

        if (DB::connection()->getDriverName() === 'mysql') {
            $table = DB::connection()->getSchemaGrammar()->wrapTable(self::TABLE);

            DB::statement("CREATE INDEX comm_logs_recipient_idx ON {$table} (recipient(191))");

            return;
        }

        Schema::table(self::TABLE, function (Blueprint $table) {
            $table->index('recipient', 'comm_logs_recipient_idx');
        });
    }
};
