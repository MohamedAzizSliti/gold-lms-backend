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
    protected $appends = ['media_path','video_path','total_duration','cover_image'];

    protected $fillable = [
        'title',
        'description',
        'slug',
        'price',
        'sale_price',
        'level',
        'language',
        'duration',
        'requirements',
        'what_you_will_learn',
        'is_featured',
        'is_published',
        'status',
        'max_students',
        'rating',
        'total_reviews',
        'total_enrollments',
        'category_id',
        'user_id',
        'media_id',
        'video_id',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'cover_image'
    ];

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
        return $this->belongsTo(User::class, 'user_id');
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
        return $this->belongsTo(Attachment::class, 'media_id');
    }

    public function mediaPath(): Attribute
    {
        return Attribute::make(
            get: function () {
                if (!$this->media_id || !$this->media) {
                    return null;
                }
                
                $media = null;
                $baseUrl = request()->getSchemeAndHttpHost();
                
                // First try to use original_url if it exists
                if ($this->media->original_url) {
                    $media = ['original_url' => $this->media->original_url];
                } 
                // Then try to construct URL from file_name
                elseif ($this->media->file_name) {
                    $filename = basename($this->media->file_name);
                    $media = ['original_url' => $baseUrl . '/admin/api/files/course-covers/' . $filename];
                }
                // Finally try to use src field
                elseif ($this->media->src && Storage::disk('public')->exists($this->media->src)) {
                    $mediaUrl = $baseUrl . '/admin' . Storage::url($this->media->src);
                    $media = ['original_url' => $mediaUrl];
                }
                
                return $media;
            }
        );
    }

    public function coverImage(): Attribute
    {
        return Attribute::make(
            get: function () {
                // First, check if we have a direct cover_image path
                if (!empty($this->cover_image)) {
                    $baseUrl = request()->getSchemeAndHttpHost();
                    return [
                        'url' => $baseUrl . $this->cover_image,
                        'path' => $this->cover_image,
                        'source' => 'direct'
                    ];
                }
                
                // Fallback to media_id approach for backward compatibility
                if ($this->media_id && $this->media) {
                    $baseUrl = request()->getSchemeAndHttpHost();
                    $filename = basename($this->media->file_name ?? '');
                    return [
                        'id' => $this->media_id,
                        'url' => $baseUrl . '/admin/api/files/course-covers/' . $filename,
                        'name' => $this->media->name ?? '',
                        'file_name' => $this->media->file_name ?? '',
                        'source' => 'media'
                    ];
                }
                
                return null;
            }
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

    /**
     * Get the revenues associated with this course through enrollments
     */
    public function revenues()
    {
        return $this->hasManyThrough(Revenue::class, Enrollment::class, 
            'course_id', 'enrollment_id', 'id', 'id');
    }
    
    /**
     * Create revenue entry for this course
     */
    public function revenue()
    {
        return $this->hasMany(Revenue::class, 'course_id');
    }
}
