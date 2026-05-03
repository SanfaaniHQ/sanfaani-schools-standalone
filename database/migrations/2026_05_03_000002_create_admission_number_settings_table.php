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
        Schema::create('admission_number_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')
                ->constrained('schools')
                ->cascadeOnDelete();
            $table->string('prefix')->nullable();
            $table->string('separator', 10)->default('/');
            $table->string('year_format', 20)->default('Y');
            $table->unsignedInteger('next_number')->default(1);
            $table->unsignedTinyInteger('padding_length')->default(3);
            $table->string('suffix')->nullable();
            $table->string('reset_cycle', 30)->default('never');
            $table->string('status', 30)->default('active');
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->unique('school_id');
            $table->index(['school_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admission_number_settings');
    }
};
