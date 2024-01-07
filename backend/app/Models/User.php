<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Laratrust\Contracts\LaratrustUser;
use Laratrust\Traits\HasRolesAndPermissions;
use Lab404\Impersonate\Models\Impersonate;

class User extends Authenticatable implements LaratrustUser
{
    use HasRolesAndPermissions;
    use Impersonate;
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'surname',
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
        'birthplace',
        'birthplace_province',
        'ssn',
        'address',
        'address_zip_code',
        'suit_size',
        'boot_size'
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
        'last_availability_change' => 'datetime',
        'birthday' => 'datetime',
        'course_date' => 'datetime'
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
