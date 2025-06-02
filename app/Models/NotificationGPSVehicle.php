<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class NotificationGPSVehicle extends Model
{

    protected $table="notifgps_vehicle";

    // Disable automatic timestamps
    public $timestamps = false;
    /**
     * The Attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'alert',
        'method',
        'max_value',
        'placeholder',
        'vehicle_id',
        'notifgps_id'
    ];



}
