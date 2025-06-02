<?php

namespace App\Models;

use App\Helpers\Helpers;
use App\Services\TrackCarService;
use Illuminate\Database\Eloquent\Model;


class Device extends Model
{

    // Disable automatic timestamps
    public $timestamps = false;
    protected $hidden = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];
    /**
     * The Attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'imei',
        'traccar_device_id',
        'uniqueId',
        'phone',
        'operator',
        'vehicle_id'
    ];

    protected $appends = [
        'info_traccar'
    ];

    public function vehicle(){
        return $this->belongsTo(Vehicle::class, 'vehicle_id' );
    }



    public function getInfoTraccarAttribute()
    {
        $traccarService = new TrackCarService();

        if (!$this->traccar_device_id){
            return false;
        }else{
            $result = Helpers::traccar_call('api/devices/'.$this->traccar_device_id,null,'GET');

            if ($result){
                // Récupérer les information qui concernet La position
                $result->{'position'} =  $traccarService->getCurrentLocation($this->traccar_device_id,true);

                return $result;
            }else{
                return false;
            }
        }
    }


}
