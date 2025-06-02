<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Course extends Model
{
    protected $guarded = ['id'];
    protected $appends = ['media_path','video_path','total_duration' ];


    // Accessor pour la durée totale
    public function totalDuration(): Attribute
    {
        return Attribute::make(
            get: function () {
                // Vérifie si les chapitres sont déjà chargés, sinon les charger
                $this->loadMissing('chapters');

                // Additionne les durées
                return $this->chapters->sum('total_duration');
            }
        );
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function instructor(): BelongsTo
    {
        return $this->belongsTo(User::class) ;
    }

    public function chapters(): HasMany
    {
        return $this->hasMany(Chapter::class);
    }


    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class);
    }

    public function media(): BelongsTo
    {
        return $this->belongsTo(Media::class);
    }

    public function mediaPath(): Attribute
    {
        $media =  null;

        if ($this->media && Storage::disk('public')->exists($this->media->src)) {
            $media = url('admin'.Storage::url($this->media->src));
            $media = ['original_url' => $media];
        }

        return Attribute::make(
            get: fn() => $media,
        );
    }

    public function videoPath(): Attribute
    {
        $media =  null;

        if ($this->video && Storage::disk('public')->exists($this->video->src)) {
            $media = url('admin'.Storage::url($this->video->src));
            $media = ['original_url' => $media];
        }

        return Attribute::make(
            get: fn() => $media,
        );
    }

    public function descriptionCourse()
    {
         return  json_decode( $this->attributes['description'],true) ;
    }

    public function getDescriptionAttribute(){
        return  json_decode($this->attributes['description']);
    }

    public function video(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'video_id');
    }

//    public function videoPath(): Attribute
//    {
//        $video = null;
//
//        if ($this->video && Storage::exists($this->video->src)) {
//            $video = Storage::url($this->video->src);
//        }
//
//        return Attribute::make(
//            get: fn() => $video,
//        );
//    }

    public function favouriteUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_courses');
    }

    public function favouriteGuests(): BelongsToMany
    {
        return $this->belongsToMany(Guest::class, 'guest_courses');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function exams(): HasMany
    {
        return $this->hasMany(Exam::class);
    }

    public function quizzes(): HasMany
    {
        return $this->hasMany(Quiz::class);
    }

    public function questions(): HasMany
    {
        return $this->hasMany(Question::class);
    }

    public function userProgress()
    {
        return $this->belongsToMany(User::class, 'user_course_progresses')->withPivot('progress', 'course_id');
    }
}
