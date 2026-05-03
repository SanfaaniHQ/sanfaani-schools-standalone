<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('platform_settings', function (Blueprint $table) {
            $table->id();
            $table->string('platform_name')->default('Sanfaani Schools');
            $table->string('company_name')->default('Sanfaani Ltd');
            $table->string('product_url')->default('https://schools.sanfaani.net');
            $table->string('main_company_url')->default('https://sanfaani.net');
            $table->string('support_email')->default('sanfaanisaas@gmail.com');
            $table->string('sales_email')->default('sanfaanisaas@gmail.com');
            $table->string('support_phone')->default('09010172138');
            $table->string('whatsapp_number')->default('+2349010172138');
            $table->string('default_country')->default('Nigeria');
            $table->string('default_currency', 10)->default('NGN');
            $table->string('default_language', 10)->default('en');
            $table->string('logo_path')->nullable();
            $table->string('favicon_path')->nullable();
            $table->string('login_background_path')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('platform_settings');
    }
};
