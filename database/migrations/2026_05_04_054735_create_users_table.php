<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('username')->unique(); 
            $table->string('phone_number')->unique(); 
            // Keep email as optional or required for backend notifications
            $table->string('email')->nullable()->unique(); 
            $table->timestamp('email_verified_at')->nullable();
            $table->timestamp('phone_verified_at')->nullable(); // For your OTP flow
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
