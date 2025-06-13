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
            $table->integer('mark_per_question')->default(1);
            $table->integer('pass_marks')->default(50);
            $table->boolean('multi_chance')->default(true);
            $table->boolean('is_published')->default(true);
            $table->integer('passing_score')->nullable();
            $table->string('status')->default('active'); // active, inactive, archived
            
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
        Schema::dropIfExists('exams');
    }
};
