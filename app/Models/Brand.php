<?php

namespace App\Models;

use App\Helpers\Helpers;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\Model;
use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Brand extends Model
{


    /**
     * The Attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'featured',
        'image',
        'created_at'
    ];


    /**
     * @return HasMany
     */
    public function model(): HasMany
    {
        return $this->hasMany(Modele::class, 'brand_id');
    }

}
