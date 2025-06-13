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
        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2)->default(0.00);
            $table->decimal('sale_price', 10, 2)->nullable();
            $table->string('level')->default('beginner'); // beginner, intermediate, advanced
            $table->string('language')->default('en');
            $table->integer('duration')->default(0); // in minutes
            $table->text('requirements')->nullable();
            $table->text('what_you_will_learn')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_published')->default(false);
            $table->string('status')->default('draft'); // draft, pending, published
            $table->integer('max_students')->nullable();
            
            // Foreign keys
            $table->foreignId('category_id')->nullable()->constrained('categories')->onDelete('set null');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // Instructor
            $table->foreignId('media_id')->nullable()->constrained('attachments')->onDelete('set null'); // Course image
            $table->foreignId('video_id')->nullable()->constrained('attachments')->onDelete('set null'); // Intro video
            
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('courses');
    }
};
