<?php

namespace App\Models;

 use App\Enums\MediaTypeEnum;
 use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Content extends Model
{
    protected $guarded = ['id'];
    protected $appends = ['media_path'];

    use HasFactory;

    protected $casts = [
        'type' => MediaTypeEnum::class
    ];

    protected static function boot()
    {
        parent::boot();

        // Order contents with serial number by default
        static::addGlobalScope('serial_number', function ($builder) {
            $builder->orderBy('serial_number');
        });
    }

    public function chapter(): BelongsTo
    {
        return $this->belongsTo(Chapter::class);
    }

    public function media(): BelongsTo
    {
        return $this->belongsTo(Media::class);
    }

    public function mediaPath(): Attribute
    {
        $media =  null;

        if ($this->media && $this->media->src && Storage::disk('public')->exists($this->media->src)) {
            $media = url('admin'.Storage::url($this->media->src));
            $media = ['original_url' => $media];
        }

        return Attribute::make(
            get: fn() => $media,
        );
    }

    public function contentViews(): HasMany
    {
        return $this->hasMany(UserContentView::class, 'content_id');
    }
}
