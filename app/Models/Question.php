<?php

namespace App\Models;

use App\Enums\QuestionTypeEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Question extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = ['id'];
   // protected $hidden = ['options']; // <-- masque le champ "options"
    protected $appends = ['option_1', 'option_2', 'option_3', 'option_4', 'correct_option', 'parent_type'];
    protected $casts = [
        'type' => QuestionTypeEnum::class,
        'options' => 'array', // or 'json'
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

    /**
     * Get option 1
     */
    public function getOption1Attribute()
    {
        return $this->extractOption('option_1');
    }

    /**
     * Get option 2
     */
    public function getOption2Attribute()
    {
        return $this->extractOption('option_2');
    }

    /**
     * Get option 3
     */
    public function getOption3Attribute()
    {
        return $this->extractOption('option_3');
    }

    /**
     * Get option 4
     */
    public function getOption4Attribute()
    {
        return $this->extractOption('option_4');
    }

    /**
     * Get the correct option key
     */
    public function getCorrectOptionAttribute()
    {
        $options = json_decode($this->attributes['options'] ?? '{}', true);
        $correct_option = null;
        foreach ($options as $key => $option) {
            if (isset($option['is_correct']) && $option['is_correct']) {
                $correct_option = $key;
            }
        }
        return $correct_option;
    }

    /**
     * Helper method to extract option by key
     */
    private function extractOption($key)
    {
        $options = json_decode($this->attributes['options'] ?? '{}', true);
        return $options[$key] ?? null;
    }
}
