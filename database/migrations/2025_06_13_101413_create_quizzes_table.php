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
        Schema::create('quizzes', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->boolean('is_published')->default(true);
            $table->integer('passing_score')->default(70);
            $table->integer('time_limit')->nullable(); // in minutes
            $table->boolean('multiple_attempts')->default(false);
            
            // Foreign keys
            $table->foreignId('course_id')->constrained('courses')->onDelete('cascade');
            $table->foreignId('media_id')->nullable()->constrained('attachments')->onDelete('set null');
            
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quizzes');
    }
};
