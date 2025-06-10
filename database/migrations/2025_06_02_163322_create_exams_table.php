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
        Schema::create('exams', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->integer('duration')->default(60); // in minutes
            $table->integer('total_marks')->default(100);
            $table->integer('passing_marks')->default(60);
            $table->integer('total_questions')->default(0);
            $table->boolean('is_published')->default(false);
            $table->boolean('shuffle_questions')->default(false);
            $table->timestamp('start_date')->nullable();
            $table->timestamp('end_date')->nullable();
            $table->integer('max_attempts')->default(1);
            $table->boolean('multi_chance')->nullable()->default(false);

            // Foreign keys
            $table->foreignId('course_id')->constrained('courses')->onDelete('cascade');

            $table->timestamps();

            // Indexes
            $table->index(['course_id', 'is_published']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exams');
    }
};
