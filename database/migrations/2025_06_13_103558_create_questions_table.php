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
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->text('question_text');
            $table->string('question_type')->default('text'); // text, code, multiple_choice, etc.
            $table->json('options')->nullable(); // For multiple choice questions
            $table->integer('correct_option')->nullable(); // For multiple choice questions
            $table->text('correct_answer')->nullable(); // For text/code questions
            $table->integer('points')->default(1);
            $table->text('explanation')->nullable(); // Explanation for the answer
            $table->integer('order')->default(0);
            
            // Polymorphic relationship - can belong to quiz or exam
            $table->foreignId('course_id')->nullable()->constrained('courses')->onDelete('cascade');
            $table->foreignId('quiz_id')->nullable()->constrained('quizzes')->onDelete('cascade');
            $table->foreignId('exam_id')->nullable()->constrained('exams')->onDelete('cascade');
            
            // Only one of quiz_id or exam_id should be set
            $table->string('questionable_type')->nullable(); // 'quiz' or 'exam'
            $table->unsignedBigInteger('questionable_id')->nullable(); // ID of the quiz or exam
            
            $table->foreignId('media_id')->nullable()->constrained('attachments')->onDelete('set null');
            
            $table->timestamps();
            $table->softDeletes();
            
            // Add indexes for better performance
            $table->index(['questionable_type', 'questionable_id']);
            $table->index(['quiz_id']);
            $table->index(['exam_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('questions');
    }
};
