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
        Schema::create('grading_scales', function (Blueprint $table) {
            $table->id();

            $table->foreignId('school_id')
                ->constrained('schools')
                ->cascadeOnDelete();

            $table->string('name')->default('Default Grading Scale');

            $table->decimal('min_score', 5, 2);
            $table->decimal('max_score', 5, 2);

            $table->string('grade');
            $table->string('remark');

            $table->boolean('is_pass')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->string('status')->default('active');

            $table->timestamps();

            $table->index(['school_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('grading_scales');
    }
};