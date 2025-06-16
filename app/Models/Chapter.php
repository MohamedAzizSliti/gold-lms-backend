<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Chapter extends Model
{
    protected $guarded = ['id'];
    // Automatically include contents count in every query
    protected $withCount = ['contents'];
    protected $appends = ['total_duration'];

    use HasFactory, SoftDeletes;

    protected static function boot()
    {
        parent::boot();

        // Order chapters with order by default
        static::addGlobalScope('order', function ($builder) {
            $builder->orderBy('order');
        });
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function contents(): HasMany
    {
        return $this->hasMany(Content::class);
    }

    public function getCommentsCountAttribute()
    {
        return $this->comments()->count(); // ⚠️ extra query
    }

    public function getTotalDurationAttribute()
    {
        return $this->contents->sum('duration');
    }
}
