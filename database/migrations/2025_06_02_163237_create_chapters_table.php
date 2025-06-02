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
        Schema::create('chapters', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->text('content')->nullable();
            $table->integer('order')->default(0);
            $table->integer('duration')->default(0); // in minutes
            $table->integer('total_duration')->default(0); // in minutes
            $table->boolean('is_free')->default(false);
            $table->boolean('is_published')->default(true);
            $table->string('video_url')->nullable();
            $table->string('video_type')->nullable(); // youtube, vimeo, upload

            // Foreign keys
            $table->foreignId('course_id')->constrained('courses')->onDelete('cascade');
            $table->foreignId('media_id')->nullable()->constrained('attachments')->onDelete('set null');

            $table->timestamps();

            // Indexes
            $table->index(['course_id', 'order']);
            $table->index(['course_id', 'is_published']);
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
