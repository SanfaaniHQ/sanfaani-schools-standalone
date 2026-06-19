<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cbt_question_banks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('subject_id')->nullable()->constrained('subjects')->nullOnDelete();
            $table->foreignId('school_class_id')->nullable()->constrained('school_classes')->nullOnDelete();
            $table->foreignId('academic_session_id')->nullable()->constrained('academic_sessions')->nullOnDelete();
            $table->foreignId('term_id')->nullable()->constrained('terms')->nullOnDelete();
            $table->string('title');
            $table->string('code', 80)->nullable();
            $table->text('description')->nullable();
            $table->string('category', 120)->nullable();
            $table->string('topic', 160)->nullable();
            $table->string('difficulty', 40)->default('mixed');
            $table->string('default_locale', 10)->default('en');
            $table->string('status', 40)->default('active');
            $table->boolean('is_reusable')->default(true);
            $table->json('tags')->nullable();
            $table->json('metadata')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['school_id', 'code'], 'cbt_question_banks_school_code_unique');
            $table->index(['school_id', 'subject_id', 'school_class_id', 'status'], 'cbt_question_banks_scope_idx');
            $table->index(['school_id', 'difficulty'], 'cbt_question_banks_pool_idx');
        });

        Schema::create('cbt_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('cbt_question_bank_id')->constrained('cbt_question_banks')->cascadeOnDelete();
            $table->foreignId('subject_id')->nullable()->constrained('subjects')->nullOnDelete();
            $table->foreignId('school_class_id')->nullable()->constrained('school_classes')->nullOnDelete();
            $table->string('question_type', 60);
            $table->longText('prompt');
            $table->longText('prompt_html')->nullable();
            $table->longText('explanation')->nullable();
            $table->string('default_locale', 10)->default('en');
            $table->string('direction', 10)->default('ltr');
            $table->string('difficulty', 40)->default('medium');
            $table->string('topic', 160)->nullable();
            $table->json('tags')->nullable();
            $table->json('content')->nullable();
            $table->json('media')->nullable();
            $table->json('scoring')->nullable();
            $table->decimal('default_marks', 8, 2)->default(1);
            $table->string('status', 40)->default('active');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['school_id', 'cbt_question_bank_id', 'status'], 'cbt_questions_bank_status_idx');
            $table->index(['school_id', 'question_type', 'difficulty', 'status'], 'cbt_questions_type_pool_idx');
            $table->index(['school_id', 'subject_id', 'school_class_id', 'topic'], 'cbt_questions_scope_topic_idx');
        });

        Schema::create('cbt_question_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('cbt_question_id')->constrained('cbt_questions')->cascadeOnDelete();
            $table->string('option_key', 20)->nullable();
            $table->longText('body');
            $table->longText('body_html')->nullable();
            $table->string('locale', 10)->default('en');
            $table->string('direction', 10)->default('ltr');
            $table->boolean('is_correct')->default(false);
            $table->decimal('score_weight', 8, 4)->default(1);
            $table->unsignedInteger('sort_order')->default(0);
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['school_id', 'cbt_question_id', 'sort_order'], 'cbt_question_options_order_idx');
        });

        Schema::create('cbt_exams', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('subject_id')->nullable()->constrained('subjects')->nullOnDelete();
            $table->foreignId('school_class_id')->nullable()->constrained('school_classes')->nullOnDelete();
            $table->foreignId('academic_session_id')->nullable()->constrained('academic_sessions')->nullOnDelete();
            $table->foreignId('term_id')->nullable()->constrained('terms')->nullOnDelete();
            $table->string('title');
            $table->string('slug', 191);
            $table->text('description')->nullable();
            $table->longText('instructions')->nullable();
            $table->string('exam_type', 60)->default('objective');
            $table->string('access_type', 60)->default('internal_student');
            $table->string('result_type', 60)->default('cbt_result');
            $table->string('status', 40)->default('draft');
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->unsignedInteger('duration_minutes')->nullable();
            $table->unsignedInteger('max_attempts')->default(1);
            $table->unsignedInteger('question_count')->default(0);
            $table->decimal('total_marks', 8, 2)->default(0);
            $table->decimal('pass_mark', 8, 2)->nullable();
            $table->boolean('randomize_questions')->default(false);
            $table->boolean('randomize_options')->default(false);
            $table->boolean('allow_resume')->default(true);
            $table->boolean('auto_submit')->default(true);
            $table->boolean('show_result_immediately')->default(false);
            $table->boolean('supports_public_candidates')->default(false);
            $table->boolean('require_fullscreen')->default(false);
            $table->timestamp('release_results_at')->nullable();
            $table->json('language_settings')->nullable();
            $table->json('anti_cheat_settings')->nullable();
            $table->json('metadata')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('published_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['school_id', 'slug'], 'cbt_exams_school_slug_unique');
            $table->index(['school_id', 'status', 'starts_at', 'ends_at'], 'cbt_exams_schedule_idx');
            $table->index(['school_id', 'subject_id', 'school_class_id', 'result_type'], 'cbt_exams_scope_idx');
        });

        Schema::create('cbt_exam_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('cbt_exam_id')->constrained('cbt_exams')->cascadeOnDelete();
            $table->foreignId('cbt_question_id')->constrained('cbt_questions')->restrictOnDelete();
            $table->string('section_title')->nullable();
            $table->decimal('marks', 8, 2)->default(1);
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_required')->default(true);
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->unique(['cbt_exam_id', 'cbt_question_id'], 'cbt_exam_questions_unique');
            $table->index(['school_id', 'cbt_exam_id', 'sort_order'], 'cbt_exam_questions_order_idx');
        });

        Schema::create('cbt_candidates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('cbt_exam_id')->constrained('cbt_exams')->cascadeOnDelete();
            $table->foreignId('student_id')->nullable()->constrained('students')->nullOnDelete();
            $table->string('name')->nullable();
            $table->string('email', 191)->nullable();
            $table->string('phone')->nullable();
            $table->string('admission_number', 100)->nullable();
            $table->string('candidate_code', 80)->unique();
            $table->string('invitation_token', 100)->nullable()->unique();
            $table->string('source', 40)->default('student');
            $table->string('status', 40)->default('invited');
            $table->timestamp('invited_at')->nullable();
            $table->timestamp('registered_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['school_id', 'cbt_exam_id', 'status'], 'cbt_candidates_exam_status_idx');
            $table->index(['school_id', 'student_id', 'cbt_exam_id'], 'cbt_candidates_student_exam_idx');
            $table->index(['school_id', 'email'], 'cbt_candidates_email_idx');
        });

        Schema::create('cbt_access_codes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('cbt_exam_id')->constrained('cbt_exams')->cascadeOnDelete();
            $table->string('code', 80)->unique();
            $table->unsignedInteger('usage_limit')->nullable();
            $table->unsignedInteger('used_count')->default(0);
            $table->string('status', 40)->default('active');
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->json('metadata')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['school_id', 'cbt_exam_id', 'status'], 'cbt_access_codes_exam_status_idx');
        });

        Schema::create('cbt_attempts', function (Blueprint $table) {
            $table->id();
            $table->uuid('attempt_uuid')->unique();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('cbt_exam_id')->constrained('cbt_exams')->cascadeOnDelete();
            $table->foreignId('cbt_candidate_id')->nullable()->constrained('cbt_candidates')->nullOnDelete();
            $table->foreignId('student_id')->nullable()->constrained('students')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedInteger('attempt_no')->default(1);
            $table->string('status', 40)->default('in_progress');
            $table->string('access_channel', 60)->default('internal');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('last_autosaved_at')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('graded_at')->nullable();
            $table->decimal('objective_score', 8, 2)->default(0);
            $table->decimal('theory_score', 8, 2)->default(0);
            $table->decimal('total_score', 8, 2)->default(0);
            $table->decimal('max_score', 8, 2)->default(0);
            $table->string('grade', 20)->nullable();
            $table->string('remark')->nullable();
            $table->string('result_release_status', 40)->default('held');
            $table->foreignId('student_result_id')->nullable()->constrained('student_results')->nullOnDelete();
            $table->char('answers_hash', 64)->nullable();
            $table->json('client_snapshot')->nullable();
            $table->json('security_snapshot')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->string('device_fingerprint', 128)->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->unique(['cbt_exam_id', 'cbt_candidate_id', 'attempt_no'], 'cbt_attempts_candidate_no_unique');
            $table->index(['school_id', 'cbt_exam_id', 'status'], 'cbt_attempts_exam_status_idx');
            $table->index(['school_id', 'student_id', 'cbt_exam_id'], 'cbt_attempts_student_exam_idx');
            $table->index(['school_id', 'result_release_status'], 'cbt_attempts_release_idx');
        });

        Schema::create('cbt_attempt_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('cbt_attempt_id')->constrained('cbt_attempts')->cascadeOnDelete();
            $table->foreignId('cbt_exam_question_id')->constrained('cbt_exam_questions')->restrictOnDelete();
            $table->foreignId('cbt_question_id')->constrained('cbt_questions')->restrictOnDelete();
            $table->string('question_type', 60);
            $table->json('answer_payload')->nullable();
            $table->longText('answer_text')->nullable();
            $table->json('selected_option_ids')->nullable();
            $table->boolean('is_correct')->nullable();
            $table->decimal('auto_score', 8, 2)->default(0);
            $table->decimal('manual_score', 8, 2)->default(0);
            $table->decimal('max_score', 8, 2)->default(0);
            $table->text('marker_comment')->nullable();
            $table->foreignId('marked_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('marked_at')->nullable();
            $table->string('status', 40)->default('draft');
            $table->timestamp('autosaved_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->unique(['cbt_attempt_id', 'cbt_exam_question_id'], 'cbt_attempt_answers_unique');
            $table->index(['school_id', 'cbt_attempt_id', 'status'], 'cbt_attempt_answers_attempt_status_idx');
            $table->index(['school_id', 'cbt_question_id'], 'cbt_attempt_answers_question_idx');
        });

        Schema::create('cbt_marking_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('cbt_exam_id')->constrained('cbt_exams')->cascadeOnDelete();
            $table->foreignId('cbt_attempt_id')->constrained('cbt_attempts')->cascadeOnDelete();
            $table->foreignId('cbt_attempt_answer_id')->constrained('cbt_attempt_answers')->cascadeOnDelete();
            $table->foreignId('marked_by')->nullable()->constrained('users')->nullOnDelete();
            $table->decimal('score', 8, 2)->default(0);
            $table->decimal('max_score', 8, 2)->default(0);
            $table->json('rubric')->nullable();
            $table->text('comments')->nullable();
            $table->string('moderation_status', 40)->default('draft');
            $table->foreignId('moderated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('moderated_at')->nullable();
            $table->boolean('is_final')->default(false);
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['school_id', 'cbt_exam_id', 'moderation_status'], 'cbt_marking_records_status_idx');
            $table->index(['cbt_attempt_answer_id', 'is_final'], 'cbt_marking_records_answer_final_idx');
        });

        Schema::create('cbt_result_publications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('cbt_exam_id')->constrained('cbt_exams')->cascadeOnDelete();
            $table->string('release_mode', 40)->default('all_attempts');
            $table->string('status', 40)->default('draft');
            $table->timestamp('published_at')->nullable();
            $table->foreignId('published_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('revoked_at')->nullable();
            $table->foreignId('revoked_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('revoke_reason')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['school_id', 'cbt_exam_id', 'status'], 'cbt_result_publications_exam_status_idx');
        });

        Schema::create('cbt_event_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->nullable()->constrained('schools')->cascadeOnDelete();
            $table->foreignId('cbt_exam_id')->nullable()->constrained('cbt_exams')->cascadeOnDelete();
            $table->foreignId('cbt_candidate_id')->nullable()->constrained('cbt_candidates')->nullOnDelete();
            $table->foreignId('cbt_attempt_id')->nullable()->constrained('cbt_attempts')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('event', 120);
            $table->string('severity', 30)->default('info');
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->json('payload')->nullable();
            $table->timestamps();

            $table->index(['school_id', 'cbt_exam_id', 'event'], 'cbt_event_logs_exam_event_idx');
            $table->index(['cbt_attempt_id', 'created_at'], 'cbt_event_logs_attempt_time_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cbt_event_logs');
        Schema::dropIfExists('cbt_result_publications');
        Schema::dropIfExists('cbt_marking_records');
        Schema::dropIfExists('cbt_attempt_answers');
        Schema::dropIfExists('cbt_attempts');
        Schema::dropIfExists('cbt_access_codes');
        Schema::dropIfExists('cbt_candidates');
        Schema::dropIfExists('cbt_exam_questions');
        Schema::dropIfExists('cbt_exams');
        Schema::dropIfExists('cbt_question_options');
        Schema::dropIfExists('cbt_questions');
        Schema::dropIfExists('cbt_question_banks');
    }
};
