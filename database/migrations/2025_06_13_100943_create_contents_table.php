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
        Schema::create('contents', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('type')->default('text'); // text, video, audio, pdf, etc
            $table->text('content')->nullable();
            $table->integer('duration')->nullable(); // in minutes
            $table->integer('serial_number')->default(0);
            $table->boolean('is_free')->default(false);
            $table->boolean('is_forwardable')->default(true);
            $table->text('media_link')->nullable();
            $table->timestamp('media_updated_at')->nullable();
            
            // Foreign keys
            $table->foreignId('chapter_id')->constrained('chapters')->onDelete('cascade');
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
        Schema::dropIfExists('contents');
    }
};
