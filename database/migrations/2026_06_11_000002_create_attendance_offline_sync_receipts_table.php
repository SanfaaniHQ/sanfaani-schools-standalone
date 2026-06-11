<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendance_offline_sync_receipts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->uuid('client_uuid');
            $table->foreignId('attendance_record_id')->nullable()->constrained('student_attendance_records')->nullOnDelete();
            $table->foreignId('processed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('payload_hash', 64);
            $table->string('result_status', 30);
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();

            $table->unique(['school_id', 'client_uuid'], 'attendance_offline_receipt_uuid_unique');
            $table->index(['school_id', 'result_status'], 'attendance_offline_receipt_status_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_offline_sync_receipts');
    }
};
