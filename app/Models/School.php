<?php

namespace App\Models;

use App\Helpers\Helpers;
use Laravel\Sanctum\HasApiTokens;
use Spatie\MediaLibrary\HasMedia;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Cviebrock\EloquentSluggable\Sluggable;
use Spatie\MediaLibrary\InteractsWithMedia;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class School extends Model implements HasMedia
{
    use Sluggable, SoftDeletes, HasApiTokens, HasFactory, Notifiable, InteractsWithMedia;

    /**
     * The stores that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'school_name',
        'description',
        'slug',
        'status',
        'country_id',
        'state_id',
        'school_logo_id',
        'school_cover_id',
        'city',
        'address',
        'pincode',
        'hide_client_email',
        'hide_client_phone',
        'facebook',
        'twitter',
        'instagram',
        'youtube',
        'pinterest',
        'client_id',
        'is_approved',
        'created_by_id',
    ];

    protected $with = [
        'school_logo',
        'client:id,name,email,country_code,phone,status',
        'country:id,name',
        'state:id,name'
    ];

    protected $withCount = [
      //  'orders',
       // 'reviews',
       // 'products'
    ];

    protected $appends = [
        //'product_images',
        //'order_amount',
        //'rating_count'
    ];

    protected $casts = [
        'country_id' => 'integer',
        'state_id' => 'integer',
        'school_logo_id' => 'integer',
        'school_cover_id' => 'integer',
        'client_id' => 'integer',
        'hide_client_email' => 'integer',
        'hide_client_phone' => 'integer',
        'status' => 'integer',
        'products_count' => 'integer',
        'is_approved' => 'integer',
        'reviews_count' => 'integer',
        'rating_count' => 'float'
    ];

    public function sluggable(): array
    {
        return [
            'slug' => [
                'source' => 'school_name',
                'onUpdate' => true,
            ]
        ];
    }

    public static function boot()
    {
        parent::boot();
        static::saving(function ($model) {
            $model->created_by_id = Helpers::getCurrentUserId() ?? $model->client_id;
        });

        static::deleted(function($store) {
          //  $store->products()->delete();
            $store->client()->delete();
        });
    }

    /**
     * @return Int
     */
    public function getId($request)
    {
        return ($request->id) ? $request->id : $request->route('school')->id;
    }

    /**
     * @return BelongsTo
     */
    public function school_logo(): BelongsTo
    {
        return $this->belongsTo(Attachment::class, 'school_logo_id');
    }

    /**
     * @return HasMany
     */
    public function orders(): HasMany
    {
       // return $this->hasMany(Order::class, 'school_id');
    }

    /**
     * @return BelongsTo
     */
    public function school_cover(): BelongsTo
    {
        return $this->belongsTo(Attachment::class, 'school_cover_id');
    }

    /**
     * @return BelongsTo
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    /**
     * @return HasMany
     */
    public function products(): HasMany
    {
       // return $this->hasMany(Product::class, 'school_id');
    }

    /**
     * @return BelongsTo
     */
    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class, 'country_id');
    }

    /**
     * @return BelongsTo
     */
    public function state(): BelongsTo
    {
        return $this->belongsTo(State::class, 'state_id');
    }

    /**
     * @return HasMany
     */
    public function reviews(): HasMany
    {
     //   return $this->hasMany(Review::class, 'store_id');
    }

    public function getProductImagesAttribute()
    {
        return Helpers::getStoreWiseLastThreeProductImages($this->id);
    }

    public function getOrdersCountAttribute()
    {
        $request = app('request');
        return (int) Helpers::getStoreOrderCount($this->id, $request->filter_by);
    }

    public function getProductsCountAttribute()
    {
        $request = app('request');
        return (int) Helpers::getProductCountByStoreId($this->id, $request->filter_by);
    }

    public function getOrderAmountAttribute()
    {
        $request = app('request');
        return (float) Helpers::countStoreOrderAmount($this->id, $request->filter_by);
    }

    public function getRatingCountAttribute()
    {
        return (float) $this->reviews->avg('rating');
    }
}
