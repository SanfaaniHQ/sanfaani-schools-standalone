<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admission_cycles', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('school_id');
            $table->string('name');
            $table->unsignedBigInteger('academic_session_id')->nullable();
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->boolean('is_open')->default(false);
            $table->json('settings')->nullable();
            $table->timestamps();

            $table->foreign('school_id', 'adm_cycle_school_fk')->references('id')->on('schools')->cascadeOnDelete();
            $table->foreign('academic_session_id', 'adm_cycle_session_fk')->references('id')->on('academic_sessions')->nullOnDelete();
            $table->index(['school_id', 'is_open'], 'adm_cycle_open_idx');
        });

        Schema::create('admission_applications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('school_id');
            $table->unsignedBigInteger('admission_cycle_id');
            $table->string('application_number', 64);
            $table->string('tracking_token', 64);
            $table->string('first_name', 100);
            $table->string('last_name', 100);
            $table->string('other_names', 150)->nullable();
            $table->string('gender', 30)->nullable();
            $table->date('date_of_birth')->nullable();
            $table->unsignedBigInteger('requested_class_id')->nullable();
            $table->string('previous_school')->nullable();
            $table->string('status', 50)->default('submitted');
            $table->string('source_channel', 100)->nullable();
            $table->string('payment_status', 30)->default('not_required');
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamp('decided_at')->nullable();
            $table->unsignedBigInteger('converted_student_id')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->foreign('school_id', 'adm_app_school_fk')->references('id')->on('schools')->cascadeOnDelete();
            $table->foreign('admission_cycle_id', 'adm_app_cycle_fk')->references('id')->on('admission_cycles')->cascadeOnDelete();
            $table->foreign('requested_class_id', 'adm_app_class_fk')->references('id')->on('school_classes')->nullOnDelete();
            $table->foreign('converted_student_id', 'adm_app_student_fk')->references('id')->on('students')->nullOnDelete();
            $table->unique('application_number', 'adm_app_number_unq');
            $table->unique('tracking_token', 'adm_app_track_unq');
            $table->index(['school_id', 'status'], 'adm_app_status_idx');
            $table->index(['school_id', 'payment_status'], 'adm_app_payment_idx');
            $table->index(['school_id', 'source_channel'], 'adm_app_source_idx');
        });

        Schema::create('admission_applicant_guardians', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('admission_application_id');
            $table->string('name');
            $table->string('relationship', 80);
            $table->string('phone', 50);
            $table->string('email')->nullable();
            $table->text('address')->nullable();
            $table->timestamps();

            $table->foreign('admission_application_id', 'adm_guard_app_fk')->references('id')->on('admission_applications')->cascadeOnDelete();
            $table->index(['admission_application_id', 'phone'], 'adm_guard_phone_idx');
        });

        Schema::create('admission_documents', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('admission_application_id');
            $table->string('document_type', 100);
            $table->string('original_name');
            $table->string('storage_path');
            $table->string('mime_type', 100);
            $table->unsignedBigInteger('size');
            $table->string('status', 30)->default('pending');
            $table->unsignedBigInteger('reviewed_by')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();

            $table->foreign('admission_application_id', 'adm_doc_app_fk')->references('id')->on('admission_applications')->cascadeOnDelete();
            $table->foreign('reviewed_by', 'adm_doc_user_fk')->references('id')->on('users')->nullOnDelete();
            $table->index(['admission_application_id', 'status'], 'adm_doc_status_idx');
        });

        Schema::create('admission_status_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('admission_application_id');
            $table->string('from_status', 50)->nullable();
            $table->string('to_status', 50);
            $table->unsignedBigInteger('changed_by')->nullable();
            $table->text('note')->nullable();
            $table->timestamps();

            $table->foreign('admission_application_id', 'adm_log_app_fk')->references('id')->on('admission_applications')->cascadeOnDelete();
            $table->foreign('changed_by', 'adm_log_user_fk')->references('id')->on('users')->nullOnDelete();
            $table->index(['admission_application_id', 'created_at'], 'adm_log_time_idx');
        });

        Schema::create('admission_notes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('admission_application_id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->text('note');
            $table->string('visibility', 20)->default('internal');
            $table->timestamps();

            $table->foreign('admission_application_id', 'adm_note_app_fk')->references('id')->on('admission_applications')->cascadeOnDelete();
            $table->foreign('user_id', 'adm_note_user_fk')->references('id')->on('users')->nullOnDelete();
        });

        Schema::create('admission_interviews', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('admission_application_id');
            $table->string('type', 30);
            $table->timestamp('scheduled_at')->nullable();
            $table->decimal('score', 8, 2)->nullable();
            $table->string('status', 30)->default('scheduled');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('admission_application_id', 'adm_int_app_fk')->references('id')->on('admission_applications')->cascadeOnDelete();
            $table->index(['admission_application_id', 'type'], 'adm_int_type_idx');
        });

        Schema::create('admission_payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('admission_application_id');
            $table->decimal('amount', 12, 2)->nullable();
            $table->string('currency', 10)->nullable();
            $table->string('method', 20)->default('manual');
            $table->string('status', 30)->default('pending');
            $table->string('reference')->nullable();
            $table->unsignedBigInteger('confirmed_by')->nullable();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamps();

            $table->foreign('admission_application_id', 'adm_pay_app_fk')->references('id')->on('admission_applications')->cascadeOnDelete();
            $table->foreign('confirmed_by', 'adm_pay_user_fk')->references('id')->on('users')->nullOnDelete();
            $table->index(['admission_application_id', 'status'], 'adm_pay_status_idx');
        });

        Schema::create('admission_channels', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('school_id');
            $table->string('name', 100);
            $table->string('type', 30);
            $table->string('allowed_domain')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('school_id', 'adm_channel_school_fk')->references('id')->on('schools')->cascadeOnDelete();
            $table->unique(['school_id', 'name'], 'adm_channel_name_unq');
            $table->index(['school_id', 'is_active'], 'adm_channel_active_idx');
        });

        Schema::create('admission_api_keys', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('school_id');
            $table->unsignedBigInteger('channel_id')->nullable();
            $table->string('name', 100);
            $table->string('key_hash', 64);
            $table->string('allowed_domain')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('school_id', 'adm_key_school_fk')->references('id')->on('schools')->cascadeOnDelete();
            $table->foreign('channel_id', 'adm_key_channel_fk')->references('id')->on('admission_channels')->nullOnDelete();
            $table->unique('key_hash', 'adm_key_hash_unq');
            $table->index(['school_id', 'is_active'], 'adm_key_active_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admission_api_keys');
        Schema::dropIfExists('admission_channels');
        Schema::dropIfExists('admission_payments');
        Schema::dropIfExists('admission_interviews');
        Schema::dropIfExists('admission_notes');
        Schema::dropIfExists('admission_status_logs');
        Schema::dropIfExists('admission_documents');
        Schema::dropIfExists('admission_applicant_guardians');
        Schema::dropIfExists('admission_applications');
        Schema::dropIfExists('admission_cycles');
    }
};
