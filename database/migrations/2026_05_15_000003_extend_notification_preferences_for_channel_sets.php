<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('notification_preferences', function (Blueprint $table) {
            if (! Schema::hasColumn('notification_preferences', 'event')) {
                $table->string('event')->nullable()->after('role');
            }

            if (! Schema::hasColumn('notification_preferences', 'channels')) {
                $table->json('channels')->nullable()->after('event');
            }

            if (! Schema::hasColumn('notification_preferences', 'enabled')) {
                $table->boolean('enabled')->nullable()->after('channels');
            }
        });
    }

    public function down(): void
    {
        Schema::table('notification_preferences', function (Blueprint $table) {
            foreach (['enabled', 'channels', 'event'] as $column) {
                if (Schema::hasColumn('notification_preferences', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
