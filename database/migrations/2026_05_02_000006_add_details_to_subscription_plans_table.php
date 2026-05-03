<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('subscription_plans', function (Blueprint $table) {
            if (! Schema::hasColumn('subscription_plans', 'name')) {
                $table->string('name')->nullable()->after('id');
            }

            if (! Schema::hasColumn('subscription_plans', 'slug')) {
                $table->string('slug')->nullable()->unique()->after('name');
            }

            if (! Schema::hasColumn('subscription_plans', 'description')) {
                $table->text('description')->nullable()->after('slug');
            }

            if (! Schema::hasColumn('subscription_plans', 'price')) {
                $table->decimal('price', 12, 2)->default(0)->after('description');
            }

            if (! Schema::hasColumn('subscription_plans', 'currency')) {
                $table->string('currency', 10)->default('NGN')->after('price');
            }

            if (! Schema::hasColumn('subscription_plans', 'pricing_model')) {
                $table->string('pricing_model', 50)->default('per_student')->after('currency');
            }

            if (! Schema::hasColumn('subscription_plans', 'billing_cycle')) {
                $table->string('billing_cycle', 50)->default('term')->after('pricing_model');
            }

            if (! Schema::hasColumn('subscription_plans', 'duration_days')) {
                $table->unsignedInteger('duration_days')->nullable()->after('billing_cycle');
            }

            if (! Schema::hasColumn('subscription_plans', 'is_trial')) {
                $table->boolean('is_trial')->default(false)->after('duration_days');
            }

            if (! Schema::hasColumn('subscription_plans', 'status')) {
                $table->string('status', 50)->default('active')->after('is_trial');
            }

            if (! Schema::hasColumn('subscription_plans', 'sort_order')) {
                $table->unsignedInteger('sort_order')->default(0)->after('status');
            }

            if (! Schema::hasColumn('subscription_plans', 'metadata')) {
                $table->json('metadata')->nullable()->after('sort_order');
            }
        });
    }

    public function down(): void
    {
        Schema::table('subscription_plans', function (Blueprint $table) {
            foreach ([
                'metadata',
                'sort_order',
                'status',
                'is_trial',
                'duration_days',
                'billing_cycle',
                'pricing_model',
                'currency',
                'price',
                'description',
                'slug',
                'name',
            ] as $column) {
                if (Schema::hasColumn('subscription_plans', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
