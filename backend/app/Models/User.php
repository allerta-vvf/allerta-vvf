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

    static function getAvailableUsers($complete = false)
    {
        $requestedCols = ['id', 'chief', 'last_access', 'name', 'surname', 'available', 'driver', 'services', 'availability_minutes'];
        if($complete) $requestedCols[] = "phone_number";

        $list = User::where('hidden', 0)
            ->select($requestedCols)
            ->orderBy('available', 'desc')
            ->orderBy('chief', 'desc')
            ->orderBy('driver', 'desc')
            ->orderBy('services', 'asc')
            ->orderBy('trainings', 'desc')
            ->orderBy('availability_minutes', 'desc')
            ->orderBy('name', 'asc')
            ->orderBy('surname', 'asc')
            ->get();

        $now = now();
        foreach($list as $user) {
            //Add online status
            $user->online = !is_null($user->last_access) && $user->last_access->diffInSeconds($now) < 30;
            //Delete last_access
            unset($user->last_access);
        }

        return $list;
    }

    static function _processUserInfo(User $user) {
        $dl_tmp = Document::where('documents.user', $user->id)
            ->where('documents.type', 'driving_license')
            ->join('document_files', 'document_files.id', '=', 'documents.document_file_id')
            ->select('documents.doc_type', 'documents.doc_number', 'documents.expiration_date', 'document_files.uuid as scan_uuid')
            ->get();

        if($dl_tmp->count() > 0) {
            $user->driving_license = $dl_tmp[0];
        }

        $tc_tmp = Document::where('documents.user', $user->id)
            ->where('documents.type', 'training_course')
            ->leftJoin('document_files', 'document_files.id', '=', 'documents.document_file_id')
            ->leftJoin('training_course_types', 'training_course_types.id', '=', 'documents.doc_type')
            ->select('documents.doc_number as doc_number', 'documents.date', 'document_files.uuid as doc_uuid', 'training_course_types.name as type')
            ->get();

        if($tc_tmp->count() > 0) {
            $user->training_courses = $tc_tmp;
            foreach($user->training_courses as $tc) {
                $tc->doc_url = !is_null($tc->doc_uuid) ?
                    URL::temporarySignedRoute(
                        'training_course_serve', now()->addHours(1), ['uuid' => $tc->doc_uuid]
                    ) : null;
                unset($tc->doc_uuid);
            }
        } else {
            $user->training_courses = [];
        }

        $me_tmp = Document::where('documents.user', $user->id)
            ->where('documents.type', 'medical_examination')
            ->leftJoin('document_files', 'document_files.id', '=', 'documents.document_file_id')
            ->select('documents.doc_certifier as certifier', 'documents.date', 'documents.expiration_date', 'document_files.uuid as cert_uuid')
            ->get();

        if($me_tmp->count() > 0) {
            $user->medical_examinations = $me_tmp;
            foreach($user->medical_examinations as $me) {
                $me->cert_url = !is_null($me->cert_uuid) ?
                    URL::temporarySignedRoute(
                        'medical_examination_serve', now()->addHours(1), ['uuid' => $me->cert_uuid]
                    ) : null;
                unset($me->cert_uuid);
            }
        } else {
            $user->medical_examinations = [];
        }

        if(!is_null($user->driving_license) && !is_null($user->driving_license->scan_uuid)) {
            $user->driving_license->scan_url = URL::temporarySignedRoute(
                'driving_license_scan_serve', now()->addMinutes(2), ['uuid' => $user->driving_license->scan_uuid]
            );
        }

        return $user;
    }

    static function getUserData(int $id)
    {
        $user = User::where('id', $id)
            ->firstOrFail();
        
        return self::_processUserInfo($user);
    }
}
