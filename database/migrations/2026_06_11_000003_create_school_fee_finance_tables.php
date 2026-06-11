<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('finance_fee_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->string('name', 120);
            $table->string('code', 50)->nullable();
            $table->text('description')->nullable();
            $table->decimal('default_amount', 12, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['school_id', 'code'], 'finance_fee_items_school_code_unique');
            $table->index(['school_id', 'is_active'], 'finance_fee_items_school_active_idx');
        });

        Schema::create('finance_fee_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('fee_item_id')->constrained('finance_fee_items')->cascadeOnDelete();
            $table->foreignId('academic_session_id')->nullable()->constrained('academic_sessions')->nullOnDelete();
            $table->foreignId('term_id')->nullable()->constrained('terms')->nullOnDelete();
            $table->foreignId('school_class_id')->nullable()->constrained('school_classes')->nullOnDelete();
            $table->foreignId('student_id')->nullable()->constrained('students')->nullOnDelete();
            $table->decimal('amount', 12, 2);
            $table->date('due_date')->nullable();
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['school_id', 'is_active'], 'finance_assign_school_active_idx');
            $table->index(['school_id', 'school_class_id'], 'finance_assign_school_class_idx');
            $table->index(['school_id', 'student_id'], 'finance_assign_school_student_idx');
        });

        Schema::create('student_fee_invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignId('school_class_id')->nullable()->constrained('school_classes')->nullOnDelete();
            $table->foreignId('academic_session_id')->nullable()->constrained('academic_sessions')->nullOnDelete();
            $table->foreignId('term_id')->nullable()->constrained('terms')->nullOnDelete();
            $table->string('invoice_number', 80);
            $table->string('status', 30)->default('issued');
            $table->decimal('total_amount', 12, 2)->default(0);
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->decimal('paid_amount', 12, 2)->default(0);
            $table->decimal('balance_amount', 12, 2)->default(0);
            $table->date('due_date')->nullable();
            $table->timestamp('issued_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->unique(['school_id', 'invoice_number'], 'student_fee_invoice_school_number_unique');
            $table->index(['school_id', 'status'], 'student_fee_invoice_school_status_idx');
            $table->index(['school_id', 'student_id'], 'student_fee_invoice_school_student_idx');
        });

        Schema::create('student_fee_invoice_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('student_fee_invoice_id')->constrained('student_fee_invoices')->cascadeOnDelete();
            $table->foreignId('fee_item_id')->nullable()->constrained('finance_fee_items')->nullOnDelete();
            $table->string('description', 160);
            $table->decimal('amount', 12, 2);
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['school_id', 'fee_item_id'], 'student_fee_item_school_fee_idx');
        });

        Schema::create('student_fee_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('student_fee_invoice_id')->nullable()->constrained('student_fee_invoices')->nullOnDelete();
            $table->foreignId('student_id')->nullable()->constrained('students')->nullOnDelete();
            $table->decimal('amount', 12, 2);
            $table->date('payment_date');
            $table->string('method', 50)->default('manual');
            $table->string('reference', 120)->nullable();
            $table->foreignId('received_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('note')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['school_id', 'payment_date'], 'student_fee_payments_school_date_idx');
            $table->index(['school_id', 'student_id'], 'student_fee_payments_school_student_idx');
            $table->index(['school_id', 'reference'], 'student_fee_payments_school_reference_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_fee_payments');
        Schema::dropIfExists('student_fee_invoice_items');
        Schema::dropIfExists('student_fee_invoices');
        Schema::dropIfExists('finance_fee_assignments');
        Schema::dropIfExists('finance_fee_items');
    }
};
