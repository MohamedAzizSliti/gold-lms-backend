<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class Modele extends Model
{
    protected $table = "models";

    /**
     * The Attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'brand_id',
        'created_at'
    ];
}
