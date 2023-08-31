<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Place extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'lat',
        'lon',
        'place_id',
        'osm_id',
        'osm_type',
        'licence',
        'addresstype',
        'country',
        'country_code',
        'name',
        'display_name',
        'road',
        'house_number',
        'postcode',
        'state',
        'village',
        'suburb',
        'city',
        'municipality'
    ];
}
