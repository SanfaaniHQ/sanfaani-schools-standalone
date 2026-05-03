<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('schools', function (Blueprint $table) {
            if (! Schema::hasColumn('schools', 'default_language')) {
                $table->string('default_language', 10)
                    ->default('en')
                    ->after('subscription_status');
            }

            if (! Schema::hasColumn('schools', 'supports_rtl')) {
                $table->boolean('supports_rtl')
                    ->default(false)
                    ->after('default_language');
            }
        });
    }

    public function down(): void
    {
        Schema::table('schools', function (Blueprint $table) {
            if (Schema::hasColumn('schools', 'supports_rtl')) {
                $table->dropColumn('supports_rtl');
            }

            if (Schema::hasColumn('schools', 'default_language')) {
                $table->dropColumn('default_language');
            }
        });
    }
};
