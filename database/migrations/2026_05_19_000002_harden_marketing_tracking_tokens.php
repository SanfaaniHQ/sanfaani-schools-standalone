<?php

use App\Models\MarketingCampaignRecipient;
use App\Support\MailSecurity;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('marketing_campaign_recipients')) {
            return;
        }

        if (! Schema::hasColumn('marketing_campaign_recipients', 'tracking_token')) {
            Schema::table('marketing_campaign_recipients', function (Blueprint $table): void {
                $table->string('tracking_token', 96)->nullable()->after('email');
            });
        }

        MarketingCampaignRecipient::query()
            ->whereNull('tracking_token')
            ->orderBy('id')
            ->chunkById(100, function ($recipients): void {
                foreach ($recipients as $recipient) {
                    $recipient->forceFill([
                        'tracking_token' => MailSecurity::trackingToken($recipient),
                    ])->saveQuietly();
                }
            });

        if (! $this->hasIndex('marketing_campaign_recipients', 'marketing_recipients_tracking_token_unique')
            && ! $this->hasDuplicateTokens()) {
            Schema::table('marketing_campaign_recipients', function (Blueprint $table): void {
                $table->unique('tracking_token', 'marketing_recipients_tracking_token_unique');
            });
        }
    }

    public function down(): void
    {
        if (! (bool) config('sanfaani.marketing.allow_destructive_rollbacks', false)
            || ! Schema::hasTable('marketing_campaign_recipients')
            || ! Schema::hasColumn('marketing_campaign_recipients', 'tracking_token')) {
            return;
        }

        Schema::table('marketing_campaign_recipients', function (Blueprint $table): void {
            if ($this->hasIndex('marketing_campaign_recipients', 'marketing_recipients_tracking_token_unique')) {
                $table->dropUnique('marketing_recipients_tracking_token_unique');
            }

            $table->dropColumn('tracking_token');
        });
    }

    private function hasIndex(string $tableName, string $indexName): bool
    {
        try {
            return collect(Schema::getIndexes($tableName))
                ->contains(fn (array $index): bool => strcasecmp((string) ($index['name'] ?? ''), $indexName) === 0);
        } catch (Throwable) {
            if (DB::getDriverName() !== 'mysql') {
                return false;
            }

            $result = DB::selectOne(
                'select index_name from information_schema.statistics where table_schema = database() and table_name = ? and index_name = ? limit 1',
                [$tableName, $indexName]
            );

            return filled($result);
        }
    }

    private function hasDuplicateTokens(): bool
    {
        try {
            return DB::table('marketing_campaign_recipients')
                ->select('tracking_token')
                ->whereNotNull('tracking_token')
                ->groupBy('tracking_token')
                ->havingRaw('count(*) > 1')
                ->exists();
        } catch (Throwable) {
            return true;
        }
    }
};
