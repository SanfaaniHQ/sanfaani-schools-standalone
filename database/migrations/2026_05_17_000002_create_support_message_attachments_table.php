<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('support_message_attachments')) {
            return;
        }

        Schema::create('support_message_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('support_message_id')->constrained('support_messages')->cascadeOnDelete();
            $table->foreignId('school_id')->nullable()->constrained('schools')->nullOnDelete();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('disk')->default('local');
            $table->string('path');
            $table->string('original_name');
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('size')->default(0);
            $table->timestamps();

            $table->index(['school_id', 'created_at'], 'sma_school_created_idx');
            $table->index(['uploaded_by', 'created_at'], 'sma_uploaded_created_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('support_message_attachments');
    }
};
