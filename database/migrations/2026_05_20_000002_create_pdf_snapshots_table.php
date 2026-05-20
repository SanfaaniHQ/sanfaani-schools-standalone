<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pdf_snapshots', function (Blueprint $table) {
            $table->id();
            $table->uuid('snapshot_uuid')->unique();
            $table->foreignId('school_id')->nullable()->constrained('schools')->restrictOnDelete();
            $table->string('snapshot_type', 80);
            $table->string('subject_type')->nullable();
            $table->unsignedBigInteger('subject_id')->nullable();
            $table->string('owner_type')->nullable();
            $table->unsignedBigInteger('owner_id')->nullable();
            $table->string('title');
            $table->string('reference_code', 100)->nullable();
            $table->unsignedInteger('snapshot_version')->default(1);
            $table->string('payload_schema_version', 80)->default('pdf_snapshot_v1');
            $table->string('locale', 10)->default('en');
            $table->string('direction', 10)->default('ltr');
            $table->json('payload');
            $table->json('branding_snapshot')->nullable();
            $table->char('snapshot_hash', 64)->unique();
            $table->string('verification_code', 120)->nullable()->unique();
            $table->string('pdf_disk')->nullable();
            $table->string('pdf_path')->nullable();
            $table->char('pdf_hash', 64)->nullable();
            $table->timestamp('pdf_generated_at')->nullable();
            $table->string('status', 40)->default('active');
            $table->foreignId('generated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('generated_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['school_id', 'snapshot_type', 'status'], 'pdf_snapshots_school_type_status_idx');
            $table->index(['subject_type', 'subject_id', 'snapshot_version'], 'pdf_snapshots_subject_version_idx');
            $table->index(['owner_type', 'owner_id'], 'pdf_snapshots_owner_idx');
            $table->index(['school_id', 'reference_code'], 'pdf_snapshots_reference_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pdf_snapshots');
    }
};
