<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inbound_leads', function (Blueprint $table) {
            $table->id();
            $table->string('name', 120);
            $table->string('email', 180)->nullable();
            $table->string('phone', 40)->nullable();
            $table->string('company', 160)->nullable();
            $table->string('service', 80);
            $table->text('message')->nullable();
            $table->string('locale', 2)->default('ka');
            $table->string('status', 24)->default('new')->index();
            $table->timestamp('consented_at');
            $table->timestamps();
            $table->index(['created_at', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inbound_leads');
    }
};
