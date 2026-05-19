<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->ensureLeadRequestMarketingColumns();
        $this->ensureMarketingEmailTemplatesTable();
        $this->ensureMarketingCampaignsTable();
        $this->ensureMarketingCampaignRecipientsTable();
        $this->ensureMarketingAutomationsTable();
        $this->ensureMarketingDeliveryEventsTable();
        $this->ensureMarketingSuppressionsTable();
    }

    public function down(): void
    {
        if ((bool) config('sanfaani.marketing.allow_destructive_rollbacks', false)) {
            Schema::dropIfExists('marketing_suppressions');
            Schema::dropIfExists('marketing_delivery_events');
            Schema::dropIfExists('marketing_automations');
            Schema::dropIfExists('marketing_campaign_recipients');
            Schema::dropIfExists('marketing_campaigns');
            Schema::dropIfExists('marketing_email_templates');
        }

        if (! (bool) config('sanfaani.marketing.allow_destructive_rollbacks', false) || ! Schema::hasTable('lead_requests')) {
            return;
        }

        if ($this->hasIndex('lead_requests', 'lead_requests_source_status_idx')) {
            Schema::table('lead_requests', function (Blueprint $table): void {
                $table->dropIndex('lead_requests_source_status_idx');
            });
        }

        foreach (['conversion_status', 'tags', 'state', 'country'] as $column) {
            if (Schema::hasColumn('lead_requests', $column)) {
                Schema::table('lead_requests', function (Blueprint $table) use ($column): void {
                    $table->dropColumn($column);
                });
            }
        }
    }

    private function ensureLeadRequestMarketingColumns(): void
    {
        if (! Schema::hasTable('lead_requests')) {
            return;
        }

        $this->addColumn('lead_requests', 'country', fn (Blueprint $table) => $table->string('country')->nullable());
        $this->addColumn('lead_requests', 'state', fn (Blueprint $table) => $table->string('state')->nullable());
        $this->addColumn('lead_requests', 'tags', fn (Blueprint $table) => $table->json('tags')->nullable());
        $this->addColumn('lead_requests', 'conversion_status', fn (Blueprint $table) => $table->string('conversion_status', 50)->nullable());

        $this->ensureIndex('lead_requests', ['source', 'status'], 'lead_requests_source_status_idx');
    }

    private function ensureMarketingEmailTemplatesTable(): void
    {
        if (! Schema::hasTable('marketing_email_templates')) {
            Schema::create('marketing_email_templates', function (Blueprint $table): void {
                $table->id();
                $table->string('name');
                $table->string('slug')->unique();
                $table->string('subject');
                $table->string('preview_text')->nullable();
                $table->longText('body');
                $table->string('status', 30)->default('active');
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
                $table->json('metadata')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->index(['status', 'created_at'], 'marketing_templates_status_created_idx');
            });

            return;
        }

        $this->addColumn('marketing_email_templates', 'name', fn (Blueprint $table) => $table->string('name')->default('Untitled template'));
        $this->addColumn('marketing_email_templates', 'slug', fn (Blueprint $table) => $table->string('slug')->nullable());
        $this->addColumn('marketing_email_templates', 'subject', fn (Blueprint $table) => $table->string('subject')->default(''));
        $this->addColumn('marketing_email_templates', 'preview_text', fn (Blueprint $table) => $table->string('preview_text')->nullable());
        $this->addColumn('marketing_email_templates', 'body', fn (Blueprint $table) => $table->longText('body')->nullable());
        $this->addColumn('marketing_email_templates', 'status', fn (Blueprint $table) => $table->string('status', 30)->default('active'));
        $this->addForeignIdColumn('marketing_email_templates', 'created_by', 'users', 'marketing_templates_created_by_fk', 'null');
        $this->addForeignIdColumn('marketing_email_templates', 'updated_by', 'users', 'marketing_templates_updated_by_fk', 'null');
        $this->addColumn('marketing_email_templates', 'metadata', fn (Blueprint $table) => $table->json('metadata')->nullable());
        $this->addTimestamps('marketing_email_templates');
        $this->addSoftDeletes('marketing_email_templates');

        $this->ensureIndex('marketing_email_templates', ['slug'], 'marketing_email_templates_slug_unique', true);
        $this->ensureIndex('marketing_email_templates', ['status', 'created_at'], 'marketing_templates_status_created_idx');
    }

    private function ensureMarketingCampaignsTable(): void
    {
        if (! Schema::hasTable('marketing_campaigns')) {
            Schema::create('marketing_campaigns', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('marketing_email_template_id')->nullable()->constrained('marketing_email_templates')->nullOnDelete();
                $table->string('name');
                $table->string('subject');
                $table->string('preview_text')->nullable();
                $table->longText('body');
                $table->string('status', 30)->default('draft');
                $table->string('target_type', 50)->default('all_leads');
                $table->json('target_filters')->nullable();
                $table->timestamp('scheduled_at')->nullable();
                $table->timestamp('sent_at')->nullable();
                $table->timestamp('paused_at')->nullable();
                $table->timestamp('archived_at')->nullable();
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
                $table->json('metadata')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->index(['status', 'scheduled_at'], 'marketing_campaigns_status_schedule_idx');
                $table->index(['target_type', 'created_at'], 'marketing_campaigns_target_created_idx');
            });

            return;
        }

        $this->addForeignIdColumn('marketing_campaigns', 'marketing_email_template_id', 'marketing_email_templates', 'marketing_campaigns_template_fk', 'null');
        $this->addColumn('marketing_campaigns', 'name', fn (Blueprint $table) => $table->string('name')->default('Untitled campaign'));
        $this->addColumn('marketing_campaigns', 'subject', fn (Blueprint $table) => $table->string('subject')->default(''));
        $this->addColumn('marketing_campaigns', 'preview_text', fn (Blueprint $table) => $table->string('preview_text')->nullable());
        $this->addColumn('marketing_campaigns', 'body', fn (Blueprint $table) => $table->longText('body')->nullable());
        $this->addColumn('marketing_campaigns', 'status', fn (Blueprint $table) => $table->string('status', 30)->default('draft'));
        $this->addColumn('marketing_campaigns', 'target_type', fn (Blueprint $table) => $table->string('target_type', 50)->default('all_leads'));
        $this->addColumn('marketing_campaigns', 'target_filters', fn (Blueprint $table) => $table->json('target_filters')->nullable());
        $this->addColumn('marketing_campaigns', 'scheduled_at', fn (Blueprint $table) => $table->timestamp('scheduled_at')->nullable());
        $this->addColumn('marketing_campaigns', 'sent_at', fn (Blueprint $table) => $table->timestamp('sent_at')->nullable());
        $this->addColumn('marketing_campaigns', 'paused_at', fn (Blueprint $table) => $table->timestamp('paused_at')->nullable());
        $this->addColumn('marketing_campaigns', 'archived_at', fn (Blueprint $table) => $table->timestamp('archived_at')->nullable());
        $this->addForeignIdColumn('marketing_campaigns', 'created_by', 'users', 'marketing_campaigns_created_by_fk', 'null');
        $this->addForeignIdColumn('marketing_campaigns', 'updated_by', 'users', 'marketing_campaigns_updated_by_fk', 'null');
        $this->addColumn('marketing_campaigns', 'metadata', fn (Blueprint $table) => $table->json('metadata')->nullable());
        $this->addTimestamps('marketing_campaigns');
        $this->addSoftDeletes('marketing_campaigns');

        $this->ensureIndex('marketing_campaigns', ['status', 'scheduled_at'], 'marketing_campaigns_status_schedule_idx');
        $this->ensureIndex('marketing_campaigns', ['target_type', 'created_at'], 'marketing_campaigns_target_created_idx');
    }

    private function ensureMarketingCampaignRecipientsTable(): void
    {
        if (! Schema::hasTable('marketing_campaign_recipients')) {
            Schema::create('marketing_campaign_recipients', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('marketing_campaign_id')->constrained('marketing_campaigns')->cascadeOnDelete();
                $table->foreignId('lead_request_id')->nullable()->constrained('lead_requests')->nullOnDelete();
                $table->string('email');
                $table->string('tracking_token', 96)->nullable()->unique('marketing_recipients_tracking_token_unique');
                $table->string('name')->nullable();
                $table->string('school_name')->nullable();
                $table->string('status', 30)->default('queued');
                $table->timestamp('queued_at')->nullable();
                $table->timestamp('sent_at')->nullable();
                $table->timestamp('opened_at')->nullable();
                $table->timestamp('clicked_at')->nullable();
                $table->timestamp('unsubscribed_at')->nullable();
                $table->text('failure_reason')->nullable();
                $table->json('metadata')->nullable();
                $table->timestamps();

                $table->unique(['marketing_campaign_id', 'email'], 'marketing_campaign_recipient_unique');
                $table->index(['status', 'queued_at'], 'marketing_recipients_status_queue_idx');
                $table->index(['email', 'status'], 'marketing_recipients_email_status_idx');
            });

            return;
        }

        $this->addForeignIdColumn('marketing_campaign_recipients', 'marketing_campaign_id', 'marketing_campaigns', 'marketing_recipients_campaign_fk');
        $this->addForeignIdColumn('marketing_campaign_recipients', 'lead_request_id', 'lead_requests', 'marketing_recipients_lead_fk', 'null');
        $this->addColumn('marketing_campaign_recipients', 'email', fn (Blueprint $table) => $table->string('email')->default(''));
        $this->addColumn('marketing_campaign_recipients', 'tracking_token', fn (Blueprint $table) => $table->string('tracking_token', 96)->nullable());
        $this->addColumn('marketing_campaign_recipients', 'name', fn (Blueprint $table) => $table->string('name')->nullable());
        $this->addColumn('marketing_campaign_recipients', 'school_name', fn (Blueprint $table) => $table->string('school_name')->nullable());
        $this->addColumn('marketing_campaign_recipients', 'status', fn (Blueprint $table) => $table->string('status', 30)->default('queued'));
        $this->addColumn('marketing_campaign_recipients', 'queued_at', fn (Blueprint $table) => $table->timestamp('queued_at')->nullable());
        $this->addColumn('marketing_campaign_recipients', 'sent_at', fn (Blueprint $table) => $table->timestamp('sent_at')->nullable());
        $this->addColumn('marketing_campaign_recipients', 'opened_at', fn (Blueprint $table) => $table->timestamp('opened_at')->nullable());
        $this->addColumn('marketing_campaign_recipients', 'clicked_at', fn (Blueprint $table) => $table->timestamp('clicked_at')->nullable());
        $this->addColumn('marketing_campaign_recipients', 'unsubscribed_at', fn (Blueprint $table) => $table->timestamp('unsubscribed_at')->nullable());
        $this->addColumn('marketing_campaign_recipients', 'failure_reason', fn (Blueprint $table) => $table->text('failure_reason')->nullable());
        $this->addColumn('marketing_campaign_recipients', 'metadata', fn (Blueprint $table) => $table->json('metadata')->nullable());
        $this->addTimestamps('marketing_campaign_recipients');

        $this->ensureIndex('marketing_campaign_recipients', ['marketing_campaign_id', 'email'], 'marketing_campaign_recipient_unique', true);
        $this->ensureIndex('marketing_campaign_recipients', ['tracking_token'], 'marketing_recipients_tracking_token_unique', true);
        $this->ensureIndex('marketing_campaign_recipients', ['status', 'queued_at'], 'marketing_recipients_status_queue_idx');
        $this->ensureIndex('marketing_campaign_recipients', ['email', 'status'], 'marketing_recipients_email_status_idx');
    }

    private function ensureMarketingAutomationsTable(): void
    {
        if (! Schema::hasTable('marketing_automations')) {
            Schema::create('marketing_automations', function (Blueprint $table): void {
                $table->id();
                $table->string('name');
                $table->string('trigger_type', 80);
                $table->string('status', 30)->default('paused');
                $table->json('audience_filters')->nullable();
                $table->json('steps')->nullable();
                $table->timestamp('last_run_at')->nullable();
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
                $table->json('metadata')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->index(['status', 'trigger_type'], 'marketing_automations_status_trigger_idx');
            });

            return;
        }

        $this->addColumn('marketing_automations', 'name', fn (Blueprint $table) => $table->string('name')->default('Untitled automation'));
        $this->addColumn('marketing_automations', 'trigger_type', fn (Blueprint $table) => $table->string('trigger_type', 80)->default('manual'));
        $this->addColumn('marketing_automations', 'status', fn (Blueprint $table) => $table->string('status', 30)->default('paused'));
        $this->addColumn('marketing_automations', 'audience_filters', fn (Blueprint $table) => $table->json('audience_filters')->nullable());
        $this->addColumn('marketing_automations', 'steps', fn (Blueprint $table) => $table->json('steps')->nullable());
        $this->addColumn('marketing_automations', 'last_run_at', fn (Blueprint $table) => $table->timestamp('last_run_at')->nullable());
        $this->addForeignIdColumn('marketing_automations', 'created_by', 'users', 'marketing_automations_created_by_fk', 'null');
        $this->addForeignIdColumn('marketing_automations', 'updated_by', 'users', 'marketing_automations_updated_by_fk', 'null');
        $this->addColumn('marketing_automations', 'metadata', fn (Blueprint $table) => $table->json('metadata')->nullable());
        $this->addTimestamps('marketing_automations');
        $this->addSoftDeletes('marketing_automations');

        $this->ensureIndex('marketing_automations', ['status', 'trigger_type'], 'marketing_automations_status_trigger_idx');
    }

    private function ensureMarketingDeliveryEventsTable(): void
    {
        if (! Schema::hasTable('marketing_delivery_events')) {
            Schema::create('marketing_delivery_events', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('marketing_campaign_id')->nullable()->constrained('marketing_campaigns')->cascadeOnDelete();
                $table->foreignId('marketing_campaign_recipient_id')->nullable();
                $table->foreignId('lead_request_id')->nullable();
                $table->string('event_type', 50);
                $table->string('email')->nullable();
                $table->text('url')->nullable();
                $table->string('ip_address', 100)->nullable();
                $table->text('user_agent')->nullable();
                $table->json('metadata')->nullable();
                $table->timestamp('occurred_at')->nullable();
                $table->timestamps();

                $table->foreign('marketing_campaign_recipient_id', 'marketing_events_recipient_fk')
                    ->references('id')
                    ->on('marketing_campaign_recipients')
                    ->cascadeOnDelete();
                $table->foreign('lead_request_id', 'marketing_events_lead_fk')
                    ->references('id')
                    ->on('lead_requests')
                    ->nullOnDelete();
                $table->index(['event_type', 'occurred_at'], 'marketing_events_type_date_idx');
                $table->index(['marketing_campaign_id', 'event_type'], 'marketing_events_campaign_type_idx');
            });

            return;
        }

        $this->addForeignIdColumn('marketing_delivery_events', 'marketing_campaign_id', 'marketing_campaigns', 'marketing_events_campaign_fk');
        $this->addForeignIdColumn('marketing_delivery_events', 'marketing_campaign_recipient_id', 'marketing_campaign_recipients', 'marketing_events_recipient_fk');
        $this->addForeignIdColumn('marketing_delivery_events', 'lead_request_id', 'lead_requests', 'marketing_events_lead_fk', 'null');
        $this->addColumn('marketing_delivery_events', 'event_type', fn (Blueprint $table) => $table->string('event_type', 50)->default('sent'));
        $this->addColumn('marketing_delivery_events', 'email', fn (Blueprint $table) => $table->string('email')->nullable());
        $this->addColumn('marketing_delivery_events', 'url', fn (Blueprint $table) => $table->text('url')->nullable());
        $this->addColumn('marketing_delivery_events', 'ip_address', fn (Blueprint $table) => $table->string('ip_address', 100)->nullable());
        $this->addColumn('marketing_delivery_events', 'user_agent', fn (Blueprint $table) => $table->text('user_agent')->nullable());
        $this->addColumn('marketing_delivery_events', 'metadata', fn (Blueprint $table) => $table->json('metadata')->nullable());
        $this->addColumn('marketing_delivery_events', 'occurred_at', fn (Blueprint $table) => $table->timestamp('occurred_at')->nullable());
        $this->addTimestamps('marketing_delivery_events');

        $this->ensureIndex('marketing_delivery_events', ['event_type', 'occurred_at'], 'marketing_events_type_date_idx');
        $this->ensureIndex('marketing_delivery_events', ['marketing_campaign_id', 'event_type'], 'marketing_events_campaign_type_idx');
    }

    private function ensureMarketingSuppressionsTable(): void
    {
        if (! Schema::hasTable('marketing_suppressions')) {
            Schema::create('marketing_suppressions', function (Blueprint $table): void {
                $table->id();
                $table->string('email')->unique();
                $table->string('reason', 80)->default('unsubscribed');
                $table->string('source', 80)->nullable();
                $table->timestamp('suppressed_at')->nullable();
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->json('metadata')->nullable();
                $table->timestamps();

                $table->index(['reason', 'suppressed_at'], 'marketing_suppressions_reason_date_idx');
            });

            return;
        }

        $this->addColumn('marketing_suppressions', 'email', fn (Blueprint $table) => $table->string('email')->default(''));
        $this->addColumn('marketing_suppressions', 'reason', fn (Blueprint $table) => $table->string('reason', 80)->default('unsubscribed'));
        $this->addColumn('marketing_suppressions', 'source', fn (Blueprint $table) => $table->string('source', 80)->nullable());
        $this->addColumn('marketing_suppressions', 'suppressed_at', fn (Blueprint $table) => $table->timestamp('suppressed_at')->nullable());
        $this->addForeignIdColumn('marketing_suppressions', 'created_by', 'users', 'marketing_suppressions_created_by_fk', 'null');
        $this->addColumn('marketing_suppressions', 'metadata', fn (Blueprint $table) => $table->json('metadata')->nullable());
        $this->addTimestamps('marketing_suppressions');

        $this->ensureIndex('marketing_suppressions', ['email'], 'marketing_suppressions_email_unique', true);
        $this->ensureIndex('marketing_suppressions', ['reason', 'suppressed_at'], 'marketing_suppressions_reason_date_idx');
    }

    private function addColumn(string $tableName, string $column, callable $definition): void
    {
        if (! Schema::hasTable($tableName) || Schema::hasColumn($tableName, $column)) {
            return;
        }

        Schema::table($tableName, function (Blueprint $table) use ($definition): void {
            $definition($table);
        });
    }

    private function addForeignIdColumn(string $tableName, string $column, string $references, string $constraintName, string $onDelete = 'cascade'): void
    {
        $this->addColumn($tableName, $column, fn (Blueprint $table) => $table->foreignId($column)->nullable());
        $this->ensureForeignKey($tableName, $column, $references, $constraintName, $onDelete);
    }

    private function addTimestamps(string $tableName): void
    {
        $this->addColumn($tableName, 'created_at', fn (Blueprint $table) => $table->timestamp('created_at')->nullable());
        $this->addColumn($tableName, 'updated_at', fn (Blueprint $table) => $table->timestamp('updated_at')->nullable());
    }

    private function addSoftDeletes(string $tableName): void
    {
        $this->addColumn($tableName, 'deleted_at', fn (Blueprint $table) => $table->timestamp('deleted_at')->nullable());
    }

    private function ensureIndex(string $tableName, array $columns, string $name, bool $unique = false): void
    {
        if (! Schema::hasTable($tableName) || $this->hasIndex($tableName, $name)) {
            return;
        }

        foreach ($columns as $column) {
            if (! Schema::hasColumn($tableName, $column)) {
                return;
            }
        }

        if ($unique && $this->hasDuplicateValues($tableName, $columns)) {
            return;
        }

        Schema::table($tableName, function (Blueprint $table) use ($columns, $name, $unique): void {
            $unique ? $table->unique($columns, $name) : $table->index($columns, $name);
        });
    }

    private function ensureForeignKey(string $tableName, string $column, string $references, string $constraintName, string $onDelete = 'cascade'): void
    {
        if (! Schema::hasTable($tableName)
            || ! Schema::hasTable($references)
            || ! Schema::hasColumn($tableName, $column)
            || $this->hasForeignKey($tableName, $constraintName, $column)
            || $this->hasOrphanedForeignKeys($tableName, $column, $references)) {
            return;
        }

        Schema::table($tableName, function (Blueprint $table) use ($column, $references, $constraintName, $onDelete): void {
            $foreign = $table->foreign($column, $constraintName)
                ->references('id')
                ->on($references);

            $onDelete === 'null' ? $foreign->nullOnDelete() : $foreign->cascadeOnDelete();
        });
    }

    private function hasIndex(string $tableName, string $indexName): bool
    {
        try {
            return collect(Schema::getIndexes($tableName))
                ->contains(fn (array $index): bool => strcasecmp((string) ($index['name'] ?? ''), $indexName) === 0);
        } catch (Throwable) {
            try {
                return Schema::hasIndex($tableName, $indexName);
            } catch (Throwable) {
                return false;
            }
        }
    }

    private function hasForeignKey(string $tableName, string $constraintName, string $column): bool
    {
        if (DB::getDriverName() === 'sqlite') {
            return false;
        }

        if (DB::getDriverName() !== 'mysql') {
            return $this->hasIndex($tableName, $constraintName);
        }

        try {
            $result = DB::selectOne(
                'select constraint_name from information_schema.key_column_usage where table_schema = database() and table_name = ? and (constraint_name = ? or column_name = ?) and referenced_table_name is not null limit 1',
                [$tableName, $constraintName, $column]
            );

            return filled($result);
        } catch (Throwable) {
            return false;
        }
    }

    private function hasDuplicateValues(string $tableName, array $columns): bool
    {
        try {
            $query = DB::table($tableName)
                ->select($columns)
                ->groupBy($columns)
                ->havingRaw('count(*) > 1');

            foreach ($columns as $column) {
                $query->whereNotNull($column);
            }

            return $query->exists();
        } catch (Throwable) {
            return true;
        }
    }

    private function hasOrphanedForeignKeys(string $tableName, string $column, string $references): bool
    {
        try {
            return DB::table($tableName)
                ->whereNotNull($column)
                ->whereNotExists(function ($query) use ($tableName, $column, $references): void {
                    $query->selectRaw('1')
                        ->from($references)
                        ->whereColumn("{$references}.id", "{$tableName}.{$column}");
                })
                ->exists();
        } catch (Throwable) {
            return true;
        }
    }
};
