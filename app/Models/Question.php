<?php

namespace App\Models;

use App\Enums\QuestionTypeEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Question extends Model
{
    use HasFactory;

    protected $guarded = ['id'];
    // protected $hidden = ['options']; // <-- masque le champ "options"
    protected $appends = ['parent_type']; // Only keep parent_type
    protected $casts = [
        'type' => QuestionTypeEnum::class,
        // Remove the array cast to keep options as JSON string
        // 'options' => 'array', 
    ];

    /**
     * Get the parent type (quiz or exam)
     */
    public function getParentTypeAttribute(): string
    {
        if ($this->quiz_id) {
            return 'quiz';
        } elseif ($this->exam_id) {
            return 'exam';
        }
        return 'unknown';
    }

    /**
     * Course relationship
     */
    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    /**
     * Exam relationship
     */
    public function exam(): BelongsTo
    {
        return $this->belongsTo(Exam::class);
    }

    /**
     * Quiz relationship
     */
    public function quiz(): BelongsTo
    {
        return $this->belongsTo(Quiz::class);
    }

    /**
     * Answer relationship
     */
    public function answers(): HasMany
    {
        return $this->hasMany(Answer::class);
    }

    /**
     * Get the parent object (quiz or exam)
     */
    public function parent()
    {
        if ($this->quiz_id) {
            return $this->quiz;
        } elseif ($this->exam_id) {
            return $this->exam;
        }
        return null;
    }
}
