<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Laratrust\Traits\LaratrustUserTrait;
use Lab404\Impersonate\Models\Impersonate;

class User extends Authenticatable
{
    use LaratrustUserTrait;
    use Impersonate;
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'username',
        'email',
        'phone_number',
        'available',
        'availability_manual_mode',
        'availability_minutes',
        'chief',
        'driver',
        'services',
        'trainings',
        'banned',
        'hidden',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'last_access' => 'datetime',
    ];

    /**
     * @return bool
     */
    public function canImpersonate()
    {
        return $this->hasPermission("users-impersonate");
    }

    /**
     * @return bool
     */
    public function canBeImpersonated()
    {
        return !$this->hasPermission("users-impersonate");
    }
}
