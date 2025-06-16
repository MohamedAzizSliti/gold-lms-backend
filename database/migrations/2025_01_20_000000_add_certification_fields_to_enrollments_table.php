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
        Schema::table('enrollments', function (Blueprint $table) {
            // Certification fields
            $table->boolean('is_certified')->default(false);
            $table->timestamp('certification_date')->nullable();
            $table->decimal('average_score', 5, 2)->nullable(); // 0-100
            $table->integer('quizzes_completed')->default(0);
            $table->integer('total_quizzes')->default(0);
            $table->foreignId('certified_by')->nullable()->constrained('users')->onDelete('set null');
            
            // Congratulations tracking
            $table->boolean('congratulations_shown')->default(false);
            
            // Feedback fields
            $table->integer('feedback_rating')->nullable(); // 1-5 stars
            $table->text('feedback_text')->nullable();
            $table->string('feedback_reaction')->nullable(); // excellent, tres_utile, stimulant, je_recommande
            $table->timestamp('feedback_submitted_at')->nullable();
            
            // Indexes
            $table->index(['is_certified', 'certification_date']);
            $table->index(['course_id', 'is_certified']);
            $table->index(['user_id', 'congratulations_shown']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('enrollments', function (Blueprint $table) {
            $table->dropForeign(['certified_by']);
            $table->dropIndex(['is_certified', 'certification_date']);
            $table->dropIndex(['course_id', 'is_certified']);
            $table->dropIndex(['user_id', 'congratulations_shown']);
            $table->dropColumn([
                'is_certified',
                'certification_date',
                'average_score',
                'quizzes_completed',
                'total_quizzes',
                'certified_by',
                'congratulations_shown',
                'feedback_rating',
                'feedback_text',
                'feedback_reaction',
                'feedback_submitted_at'
            ]);
        });
    }
}; 