<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('school_result_settings')) {
            return;
        }

        Schema::create('school_result_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->decimal('pass_mark', 5, 2)->default(40);
            $table->decimal('maximum_score', 5, 2)->default(100);
            $table->decimal('ca_max_score', 5, 2)->nullable();
            $table->decimal('exam_max_score', 5, 2)->nullable();
            $table->string('default_result_type', 50)->default('term_result');
            $table->boolean('require_all_subjects')->default(true);
            $table->boolean('show_positions')->default(true);
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->unique('school_id', 'srs_school_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('school_result_settings');
    }
};
