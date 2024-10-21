<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\PlaceProvince;

class PlaceMunicipality extends Model
{
    use HasFactory;

    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'code', 'name', 'foreign_name', 'cadastral_code', 'postal_code', 'prefix', 'email', 'pec', 'phone', 'fax', 'latitude', 'longitude'
    ];

    /**
     * Get the province
     */
    public function province(): BelongsTo
    {
        return $this->belongsTo(PlaceProvince::class);
    }
}
