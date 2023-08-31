<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Service extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'code',
        'chief',
        //'place', //TODO: replace with a table
        'notes',
        //'type' //TODO: add table
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'start' => 'datetime',
        'end' => 'datetime'
    ];

    public function chief(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function drivers(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            'services_drivers',
            'service_id',
            'user_id'
        );
    }

    public function crew(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            'services_crew',
            'service_id',
            'user_id'
        );
    }

    public function type(): BelongsTo
    {
        return $this->belongsTo(ServiceType::class);
    }

    public function place(): BelongsTo
    {
        return $this->belongsTo(Place::class);
    }

    /**
     * Get the user that added the service.
     */
    public function addedBy(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the user that added the service.
     */
    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the user that added the service.
     */
    public function deletedBy(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
