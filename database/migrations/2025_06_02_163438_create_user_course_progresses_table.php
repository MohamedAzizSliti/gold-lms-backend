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
        Schema::create('user_course_progresses', function (Blueprint $table) {
            $table->id();
            $table->decimal('progress', 5, 2)->default(0); // percentage 0-100
            $table->timestamp('last_accessed_at')->nullable();
            $table->integer('completed_chapters')->default(0);
            $table->integer('total_chapters')->default(0);
            $table->text('notes')->nullable();

            // Foreign keys
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('course_id')->constrained('courses')->onDelete('cascade');

            $table->timestamps();

            // Unique constraint
            $table->unique(['user_id', 'course_id']);

            // Indexes
            $table->index(['user_id', 'progress']);
            $table->index(['course_id', 'progress']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_course_progresses');
    }
};
