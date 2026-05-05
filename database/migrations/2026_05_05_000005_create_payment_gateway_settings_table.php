<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_gateway_settings', function (Blueprint $table) {
            $table->id();
            $table->string('gateway', 50);
            $table->string('mode', 20)->default('test');
            $table->boolean('is_enabled')->default(false);
            $table->text('public_key')->nullable();
            $table->text('secret_key')->nullable();
            $table->text('encryption_key')->nullable();
            $table->text('webhook_secret')->nullable();
            $table->string('callback_url')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->unique(['gateway', 'mode'], 'pgs_gateway_mode_unq');
            $table->index(['is_enabled', 'mode'], 'pgs_enabled_mode_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_gateway_settings');
    }
};
