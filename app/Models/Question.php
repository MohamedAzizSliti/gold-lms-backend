<?php

namespace App\Models;

use App\Enum\QuestionTypeEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Question extends Model
{
    protected $guarded = ['id'];
   // protected $hidden = ['options']; // <-- masque le champ "options"
    protected $appends = ['option_1', 'option_2', 'option_3', 'option_4','correct_option'];
    protected $casts = [
        'type' => QuestionTypeEnum::class,
        'options' => 'array', // or 'json'
    ];



    use HasFactory;

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function exam(): BelongsTo
    {
        return $this->belongsTo(Exam::class);
    }

    public function quiz(): BelongsTo
    {
        return $this->belongsTo(Quiz::class);
    }

    public function answers(): HasMany
    {
        return $this->hasMany(Answer::class);
    }



    public function getOption1Attribute()
    {
        return $this->extractOption('option_1');
    }

    public function getOption2Attribute()
    {
        return $this->extractOption('option_2');
    }

    public function getOption3Attribute()
    {
        return $this->extractOption('option_3');
    }

    public function getCorrectOptionAttribute()
    {
        $options = json_decode($this->attributes['options'] ?? '{}', true);
        $correct_option = null;
        foreach ($options as $key => $option){
            if ($option['is_correct']){
                $correct_option = $key;
            }
        }
        return $correct_option;
    }

    public function getOption4Attribute()
    {
        return $this->extractOption('option_4');
    }

    private function extractOption($key)
    {
         $options = json_decode($this->attributes['options'] ?? '{}', true);
         return $options[$key] ?? null;
    }

}
