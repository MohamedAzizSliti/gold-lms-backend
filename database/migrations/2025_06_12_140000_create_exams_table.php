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
            $table->unsignedBigInteger('course_id');
            $table->string('title');
            $table->integer('duration')->comment('Duration in minutes');            $table->decimal('mark_per_question', 8, 2)->default(1.0)->nullable(false);
            $table->decimal('pass_marks', 8, 2)->default(50.0)->nullable(false);
            $table->boolean('multi_chance')->default(false);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('course_id')
                ->references('id')
                ->on('courses')
                ->onDelete('cascade')
                ->onUpdate('cascade');
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
