<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mail_settings', function (Blueprint $table) {
            $table->foreignId('school_id')->nullable()->after('id')->constrained()->nullOnDelete();
            $table->string('reply_to_email')->nullable()->after('from_name');
            $table->index(['school_id', 'is_enabled']);
        });
    }

    public function down(): void
    {
        Schema::table('mail_settings', function (Blueprint $table) {
            $table->dropIndex(['school_id', 'is_enabled']);
            $table->dropConstrainedForeignId('school_id');
            $table->dropColumn('reply_to_email');
        });
    }
};
