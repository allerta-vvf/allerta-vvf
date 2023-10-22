<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Alert extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'type',
        'closed',
        'notes'
    ];

    public function crew(): BelongsToMany
    {
        return $this->belongsToMany(
            AlertCrew::class,
            'alerts_crew_associations'
        );
    }

    /**
     * Get the user.
     */
    public function addedBy(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the user.
     */
    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
