<?php

namespace App\Models;

use App\Helpers\Helpers;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\Model;
use App\Enums\RoleEnum;
use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Vehicle extends Model
{
    use SoftDeletes;

    /**
     * The Attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'brand_id',
        'model_id',
        'driver_id',
        'title',
        'status',
        'type',
        'color',
        'device_id',
        'description',
        'created_by_id',
        'immatriculation',
        'vehicle_thumbnail_id',
        'vehicle_galleries_id',
        'size_chart_image_id',
        'image',
        'speed_max',
        'carburant',
        'coso_moy_carburant'
    ];
    protected $casts = [
        'brand_id' => 'integer',
        'model_id' => 'integer',
        'coso_moy_carburant' => 'float'
    ];
    protected $with = [
        'vehicle_thumbnail'
    ];

    /**
     *
     */
    public static function boot()
    {
        parent::boot();
        static::saving(function ($model) {
            $model->created_by_id = Helpers::getCurrentUserId();
        });

        static::deleted(function($vehicle) {
            $device = Device::where('vehicle_id',$vehicle->id)->first();

            $vehicle->device()->delete();
            // supprimer aussi from Traccar
            if($device && $device->traccar_device_id){
                Helpers::traccar_call('api/devices/'.$device->traccar_device_id,null,'DELETE');
            }
            // Supprimer le paramétrrage des Notifs
            $vehicle->notifs()->detach();
        });
    }

    public function driver(){
        return $this->belongsTo(User::class, 'driver_id');
    }


    public function device(){
        return $this->belongsTo(Device::class, 'device_id');
    }

    public function brand(){
        return $this->belongsTo(Brand::class, 'brand_id');
    }

    public function model(){
        return $this->belongsTo(Modele::class, 'model_id');
    }

    public function user(){
        return $this->belongsTo(User::class, 'created_by_id');
    }

    /**
     * Accessor: Get the tags field as an array
     */
    public function getVehicleGalleriesIdAttribute($value)
    {
        return json_decode($value, true); // Decode JSON to array
    }

    /**
     * Mutator: Set the tags field as a JSON string
     */
    public function stVehicleGalleriesIdAttribute($value)
    {
        $this->attributes['tags'] = json_encode($value); // Encode array to JSON
    }

    /**
     * @return BelongsTo
     */
    public function vehicle_thumbnail(): BelongsTo
    {
        return $this->belongsTo(Attachment::class, 'vehicle_thumbnail_id');
    }

    /**
     * @return BelongsTo
     */
    public function size_chart_image(): BelongsTo
    {
        return $this->belongsTo(Attachment::class, 'size_chart_image_id');
    }

    /**
     * @return BelongsToMany
     */
    public function notifs(): BelongsToMany
    {
        return $this->belongsToMany(NotificationGPS::class, 'notifgps_vehicle','vehicle_id','notifgps_id')->withPivot('alert','method','max_value','placeholder');
    }

    /**
     * @return Int
     */
    public function getId($request)
    {
        return ($request->id) ? $request->id : $request->route('product')->id;
    }

    public function scopeNotDeleted($query)
    {
        return $query->whereNull('deleted_at');
    }

    public function scopeByRole($query, $roleName, $userId)
    {
        if ($roleName == RoleEnum::CONSUMER) {
            return $query->where('created_by_id', $userId);
        }

        return $query;
    }




}
