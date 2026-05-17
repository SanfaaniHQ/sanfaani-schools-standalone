<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('users') || Schema::hasColumn('users', 'preferred_locale')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            $table->string('preferred_locale', 10)->nullable()->after('must_change_password');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('users') || ! Schema::hasColumn('users', 'preferred_locale')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('preferred_locale');
        });
    }
};
