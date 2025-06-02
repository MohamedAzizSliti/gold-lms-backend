<?php

namespace App\Models;

use App\Helpers\Helpers;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\Model;
use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Reminder extends Model
{


    public $timestamps = true;
    /**
     * The Attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'title',
        'kilometre',
        'date',
        'vehicle_id',
        'user_id',
        'description',
        'alert_sms'
    ];


    /**
     *
     */
    public static function boot()
    {
        parent::boot();
        static::saving(function ($model) {
            $model->user_id = Helpers::getCurrentUserId();
        });
    }

    /**
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * @return BelongsTo
     */
    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class, 'vehicle_id');
    }

    /**
     * @return Int
     */
    public function getId($request)
    {
       return $request->id;
    }

}
