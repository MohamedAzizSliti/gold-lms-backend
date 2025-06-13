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
        // Drop existing chapters tables to avoid conflicts
        Schema::dropIfExists('chapters');
        
        // Create chapters table with simple structure
        Schema::create('chapters', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->integer('serial_number')->default(0);
            $table->unsignedBigInteger('course_id');
            $table->timestamps();
            
            // No foreign keys to avoid issues
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chapters');
    }
};
