<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('marketing_unsubscribes', function (Blueprint $table): void {
            $table->id();
            $table->string('email')->nullable();
            $table->string('email_hash', 64)->nullable();
            $table->string('token_hash', 64)->nullable();
            $table->string('reason')->nullable();
            $table->string('source')->nullable();
            $table->timestamp('unsubscribed_at');
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->unique('email_hash', 'marketing_unsubscribes_email_hash_unique');
            $table->index('token_hash', 'marketing_unsubscribes_token_hash_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('marketing_unsubscribes');
    }
};
