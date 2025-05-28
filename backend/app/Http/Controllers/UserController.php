<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Document;
use App\Models\DocumentFile;
use App\Services\Logger;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\URL;

use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

use App\Http\Resources\UsersListResource;
use App\Http\Resources\UserResource;


class UserController extends Controller
{
    /**
     * Return users list.
     *
     * Used in main list
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        User::where('id', $request->user()->id)->update(['last_access' => now()]);
        
        return UsersListResource::collection(
            User::getAvailableUsers($request->user()->isAbleTo("users-read"))
        );
    }

    /**
     * Return a single user with all the details.
     */
    public function show(Request $request, User $user): UserResource
    {
        if($request->user()->id != $user->id && !$request->user()->hasPermission("users-read")) abort(401);

        User::where('id', $request->user()->id)->update(['last_access' => now()]);

        return UserResource::make(
            User::_processUserInfo($user)
        );
    }

    /**
     * Update user data
     */
    public function update(Request $request, User $user)
    {
        if($request->user()->id != $user->id && !$request->user()->hasPermission("users-update")) abort(401);
        if($request->user()->id == $user->id && !$request->user()->hasPermission("user-update")) abort(401);

        $request->validate([
            'name' => 'required|string|max:255',
            'surname' => 'nullable|string|max:255',
            'username' => 'required|string|max:255|unique:users,username,' . $user->id,
            'birthday' => 'nullable|date',
            'birthplace' => 'nullable|string|max:255',
            'birthplace_province' => 'nullable|string|max:255',
            'ssn' => 'nullable|string|max:255',
            'course_date' => 'nullable|date',
            'driver' => 'boolean',
            'chief' => 'boolean',
            'banned' => 'nullable|boolean',
            'hidden' => 'nullable|boolean',
            'driving_license' => 'nullable|array',
            'driving_license.number' => 'nullable|alpha_num|max:255',
            'driving_license.type' => 'nullable|string|max:255',
            'driving_license.expiration_date' => 'nullable|date',
            'driving_license.scan' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:255',
            'address_zip_code' => 'nullable|integer|max:255',
            'phone_number' => 'nullable|string|max:255',
            'email' => 'nullable|string|max:255',
            'suit_size' => 'nullable|string|max:255',
            'boot_size' => 'nullable|string|max:255'
        ]);

        $canSetChief = $request->user()->id == $user->id ?
            $request->user()->hasPermission("user-set-chief") :
            $request->user()->hasPermission("users-set-chief");
        $canSetDriver = $request->user()->id == $user->id ?
            $request->user()->hasPermission("user-set-driver") :
            $request->user()->hasPermission("users-set-driver");
        $canBan = $request->user()->id == $user->id ?
            $request->user()->hasPermission("user-ban") :
            $request->user()->hasPermission("users-ban");
        $canHide = $request->user()->id == $user->id ?
            $request->user()->hasPermission("user-hide") :
            $request->user()->hasPermission("users-hide");
        if(!$canSetChief) $request->request->remove('chief');
        if(!$canSetDriver) $request->request->remove('driver');
        if(!$canBan) $request->request->remove('banned');
        if(!$canHide) $request->request->remove('hidden');

        //Check if chief attribute is present and changed from previous value
        if($request->has('chief') && $request->chief != $user->chief) {
            if($request->chief) {
                //Add role chief to the user
                $user->addRole('chief');
            } else {
                //Remove role chief from the user
                $user->removeRole('chief');
            }
        }

        $user->update($request->all());

        if($request->has('birthday')) {
            $user->birthday = \Carbon\Carbon::parse($request->birthday);
            $user->save();
        }
        if($request->has('course_date')) {
            $user->course_date = \Carbon\Carbon::parse($request->course_date);
            $user->save();
        }

        //Check if driving license is present
        if($request->has('driving_license')) {
            $drivingLicense = Document::where('user', $user->id)
                ->where('type', 'driving_license')
                ->first();

            if(is_null($drivingLicense)) {
                $drivingLicense = new Document();
                $drivingLicense->user = $user->id;
                $drivingLicense->added_by = $request->user()->id;
                $drivingLicense->type = 'driving_license';
            }

            if ($request->has('driving_license.number')) {
                $drivingLicense->doc_number = $request->driving_license['number'];
            }
            if ($request->has('driving_license.type')) {
                $drivingLicense->doc_type = $request->driving_license['type'];
            }
            if ($request->has('driving_license.expiration_date')) {
                $drivingLicense->expiration_date = \Carbon\Carbon::parse($request->driving_license['expiration_date']);
            }
            if($request->has('driving_license.scan') && !is_null($request->driving_license['scan'])) {
                $documentFile = DocumentFile::where('uuid', $request->driving_license['scan'])->first();
                //Ensure that the document file exists
                if(is_null($documentFile)) {
                    return response()->json([
                        'message' => 'Document file not found'
                    ], Response::HTTP_NOT_FOUND);
                }
                $drivingLicense->documentFile()->associate($documentFile);
            }
            $drivingLicense->save();
        }

        Logger::log("Modifica profilo utente", $user);

        return response()->noContent();
    }

    /**
     * Update user password
     */
    public function updatePassword(Request $request, User $user)
    {
        if($request->user()->id != $user->id && !$request->user()->hasPermission("users-update-auth")) abort(401);
        if($request->user()->id == $user->id && !$request->user()->hasPermission("user-update-auth")) abort(401);

        $request->validate([
            'password' => 'required|string|min:6'
        ]);

        $user->password = bcrypt($request->password);
        $user->save();

        Logger::log("Modifica password utente", $user);

        return response()->noContent();
    }
}
