<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScheduleSlots extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'day',
        'slot'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
    ];

    /**
     * Get the user that owns the phone.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    protected static function defAttr($messages, $attribute){

        if(isset($messages[$attribute])){
            return $messages[$attribute];
        }

        $attributes = [ 
            "user" => auth()->id(),
        ];

        return $attributes[$attribute];
    }
    
    protected static function booted()
    {
        static::creating(function ($messages) {
            $messages->user = self::defAttr($messages, "user");
        });
    }
}
