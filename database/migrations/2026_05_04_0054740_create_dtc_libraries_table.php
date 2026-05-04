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
        Schema::create('dtc_libraries', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // e.g., P0301
            $table->text('description');
            $table->text('vag_specific_info'); // Focus on VW/Audi specific engine logic
            $table->enum('severity', ['low', 'medium', 'high']); 
            $table->jsonb('possible_causes')->nullable(); // PostgreSQL JSONB for engine parts
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dtc_libraries');
    }
};
