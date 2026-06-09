<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('password_reset_otps', function (Blueprint $table) {
            if (!Schema::hasColumn('password_reset_otps', 'email')) {
                $table->string('email')->nullable()->after('id');
            }

            if (!Schema::hasColumn('password_reset_otps', 'verified_at')) {
                $table->timestamp('verified_at')->nullable()->after('expires_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('password_reset_otps', function (Blueprint $table) {
            if (Schema::hasColumn('password_reset_otps', 'email')) {
                $table->dropColumn('email');
            }
        });
    }
};