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
            $table->foreignId('chapter_id')->constrained('chapters')->onDelete('cascade');
            $table->foreignId('media_id')->nullable()->constrained('media')->onDelete('set null');
            $table->string('title');
            $table->enum('type', ['video', 'text', 'pdf', 'audio']);
            $table->integer('duration')->default(0);
            $table->integer('serial_number');
            $table->boolean('is_forwardable')->default(false);
            $table->boolean('is_free')->default(false);
            $table->string('media_link')->nullable();
            $table->timestamp('media_updated_at')->nullable();
            $table->timestamps();
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
