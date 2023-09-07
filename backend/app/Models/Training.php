<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Training extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'place',
        'notes'
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

    public function crew(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            'trainings_crew',
            'training_id',
            'user_id'
        );
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
}
