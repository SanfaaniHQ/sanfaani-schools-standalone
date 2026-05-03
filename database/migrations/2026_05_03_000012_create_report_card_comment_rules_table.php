<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('report_card_comment_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->string('comment_type');
            $table->decimal('min_average', 5, 2);
            $table->decimal('max_average', 5, 2);
            $table->text('comment');
            $table->string('status')->default('active');
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->index(['school_id', 'comment_type', 'status'], 'report_comment_type_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('report_card_comment_rules');
    }
};
