<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('system_versions', function (Blueprint $table) {
            $table->id();
            $table->string('version', 50);
            $table->string('channel', 50)->default('stable');
            $table->date('release_date')->nullable();
            $table->boolean('is_current')->default(false);
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['is_current', 'channel']);
            $table->unique(['version', 'channel']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('system_versions');
    }
};
