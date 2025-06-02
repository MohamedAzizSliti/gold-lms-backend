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
            $table->text('question');
            $table->string('type')->default('multiple_choice'); // multiple_choice, true_false, short_answer, essay
            $table->json('options')->nullable(); // for multiple choice questions
            $table->text('correct_answer')->nullable();
            $table->text('explanation')->nullable();
            $table->integer('marks')->default(1);
            $table->integer('order')->default(0);
            $table->boolean('is_active')->default(true);

            // Foreign keys
            $table->foreignId('course_id')->constrained('courses')->onDelete('cascade');
            $table->foreignId('exam_id')->nullable()->constrained('exams')->onDelete('cascade');
            $table->foreignId('quiz_id')->nullable()->constrained('quizzes')->onDelete('cascade');

            $table->timestamps();

            // Indexes
            $table->index(['course_id', 'is_active']);
            $table->index(['exam_id', 'order']);
            $table->index(['quiz_id', 'order']);
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
