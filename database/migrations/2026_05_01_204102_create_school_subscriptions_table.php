<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('school_subscriptions')) {
            $this->assertExistingTableMatchesMigration();

            return;
        }

        Schema::create('school_subscriptions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('school_id')
                ->constrained('schools')
                ->cascadeOnDelete();

            $table->foreignId('subscription_plan_id')
                ->constrained('subscription_plans')
                ->cascadeOnDelete();

            $table->string('status', 50)->default('active');

            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->timestamp('trial_ends_at')->nullable();
            $table->timestamp('grace_ends_at')->nullable();

            $table->string('billing_cycle', 50)->default('term');
            $table->string('pricing_model', 50)->default('per_student');

            $table->decimal('price', 12, 2)->default(0);
            $table->string('currency', 10)->default('NGN');

            $table->unsignedInteger('student_count')->nullable();

            $table->decimal('amount_due', 12, 2)->default(0);
            $table->decimal('amount_paid', 12, 2)->default(0);

            $table->string('payment_status', 50)->default('pending');
            $table->string('payment_reference')->nullable();

            $table->foreignId('activated_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->foreignId('upgraded_from_subscription_id')
                ->nullable()
                ->constrained('school_subscriptions')
                ->nullOnDelete();

            $table->foreignId('downgraded_from_subscription_id')
                ->nullable()
                ->constrained('school_subscriptions')
                ->nullOnDelete();

            $table->foreignId('superseded_by_subscription_id')
                ->nullable()
                ->constrained('school_subscriptions')
                ->nullOnDelete();

            $table->string('plan_name_snapshot')->nullable();
            $table->decimal('price_snapshot', 12, 2)->nullable();
            $table->string('billing_cycle_snapshot', 50)->nullable();
            $table->string('pricing_model_snapshot', 50)->nullable();

            $table->json('features_snapshot')->nullable();
            $table->json('metadata')->nullable();

            $table->timestamps();

            $table->index(
                ['school_id', 'status'],
                'school_subscriptions_school_status_index'
            );

            $table->index(
                ['subscription_plan_id', 'status'],
                'school_subscriptions_plan_status_index'
            );

            $table->index(
                ['starts_at', 'ends_at'],
                'school_subscriptions_period_index'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('school_subscriptions');
    }

    private function assertExistingTableMatchesMigration(): void
    {
        $missingColumns = array_values(array_filter(
            $this->requiredColumns(),
            fn (string $column): bool => ! Schema::hasColumn('school_subscriptions', $column)
        ));

        $missingIndexes = array_values(array_filter(
            $this->requiredIndexes(),
            fn (string $index): bool => ! $this->hasIndex($index)
        ));

        $missingForeignKeys = array_values(array_filter(
            $this->requiredForeignKeys(),
            fn (array $foreignKey): bool => ! $this->hasForeignKey($foreignKey['column'], $foreignKey['table'])
        ));

        if ($missingColumns === [] && $missingIndexes === [] && $missingForeignKeys === []) {
            return;
        }

        $problems = [];

        if ($missingColumns !== []) {
            $problems[] = 'missing columns: '.implode(', ', $missingColumns);
        }

        if ($missingIndexes !== []) {
            $problems[] = 'missing indexes: '.implode(', ', $missingIndexes);
        }

        if ($missingForeignKeys !== []) {
            $problems[] = 'missing foreign keys: '.implode(', ', array_map(
                fn (array $foreignKey): string => "{$foreignKey['column']} -> {$foreignKey['table']}.id",
                $missingForeignKeys
            ));
        }

        throw new RuntimeException(
            'school_subscriptions exists but does not match 2026_05_01_204102_create_school_subscriptions_table; '.
            implode('; ', $problems).
            '. Refusing to mark an incomplete schema as migrated.'
        );
    }

    /**
     * @return array<int, string>
     */
    private function requiredColumns(): array
    {
        return [
            'id',
            'school_id',
            'subscription_plan_id',
            'status',
            'starts_at',
            'ends_at',
            'trial_ends_at',
            'grace_ends_at',
            'billing_cycle',
            'pricing_model',
            'price',
            'currency',
            'student_count',
            'amount_due',
            'amount_paid',
            'payment_status',
            'payment_reference',
            'activated_by',
            'upgraded_from_subscription_id',
            'downgraded_from_subscription_id',
            'superseded_by_subscription_id',
            'plan_name_snapshot',
            'price_snapshot',
            'billing_cycle_snapshot',
            'pricing_model_snapshot',
            'features_snapshot',
            'metadata',
            'created_at',
            'updated_at',
        ];
    }

    /**
     * @return array<int, string>
     */
    private function requiredIndexes(): array
    {
        return [
            'school_subscriptions_school_status_index',
            'school_subscriptions_plan_status_index',
            'school_subscriptions_period_index',
        ];
    }

    /**
     * @return array<int, array{column: string, table: string}>
     */
    private function requiredForeignKeys(): array
    {
        return [
            ['column' => 'school_id', 'table' => 'schools'],
            ['column' => 'subscription_plan_id', 'table' => 'subscription_plans'],
        ];
    }

    private function hasIndex(string $index): bool
    {
        try {
            return Schema::hasIndex('school_subscriptions', $index);
        } catch (Throwable) {
            return false;
        }
    }

    private function hasForeignKey(string $column, string $referencedTable): bool
    {
        try {
            $foreignKeys = Schema::getForeignKeys('school_subscriptions');
        } catch (Throwable) {
            return false;
        }

        foreach ($foreignKeys as $foreignKey) {
            if (
                in_array($column, $foreignKey['columns'] ?? [], true)
                && ($foreignKey['foreign_table'] ?? null) === $referencedTable
            ) {
                return true;
            }
        }

        return false;
    }
};
