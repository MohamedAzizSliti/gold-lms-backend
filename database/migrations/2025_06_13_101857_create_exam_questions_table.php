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
        Schema::create('exam_questions', function (Blueprint $table) {
            $table->id();
            $table->text('question');
            $table->string('type')->default('text'); // text, code, multiple_choice
            $table->json('options')->nullable(); // For multiple choice questions
            $table->integer('correct_option')->nullable(); // Index of correct option for multiple choice
            $table->text('correct_answer')->nullable(); // For text or code questions
            $table->integer('points')->default(1);
            $table->text('explanation')->nullable(); // Explanation for the answer
            $table->integer('order')->default(0);
            
            // Foreign keys
            $table->foreignId('exam_id')->constrained('exams')->onDelete('cascade');
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
        Schema::dropIfExists('exam_questions');
    }
};
