<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Document;
use App\Models\DocumentFile;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\URL;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $requestedCols = ['id', 'chief', 'last_access', 'name', 'available', 'driver', 'services', 'availability_minutes'];
        if($request->user()->isAbleTo("users-read")) $requestedCols[] = "phone_number";

        User::where('id', $request->user()->id)->update(['last_access' => now()]);

        $list = User::where('hidden', 0)
            ->select($requestedCols)
            ->orderBy('available', 'desc')
            ->orderBy('chief', 'desc')
            ->orderBy('driver', 'desc')
            ->orderBy('services', 'asc')
            ->orderBy('trainings', 'desc')
            ->orderBy('availability_minutes', 'desc')
            ->orderBy('name', 'asc')
            ->get();
        
        $now = now();
        foreach($list as $user) {
            //Add online status
            $user->online = !is_null($user->last_access) && $user->last_access->diffInSeconds($now) < 30;
            //Delete last_access
            unset($user->last_access);
        }

        //TODO: support for more data selections, follow user permissions, do not share information that should not be shared to that user etc. see notes

        return response()->json($list);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, User $user)
    {
        User::where('id', $request->user()->id)->update(['last_access' => now()]);

        //TODO: do not display useless or hidden info to the user about documentFile (like filePath and id) but only the url (see below)
        $user->driving_license = Document::where('added_by', $user->id)
            ->where('type', 'driving_license')
            ->with('documentFile')
            ->first();

        if(!is_null($user->driving_license) && !is_null($user->driving_license->documentFile)) {
            $user->driving_license->documentFile->url = URL::temporarySignedRoute(
                'driving_license_scan_serve', now()->addMinutes(2), ['uuid' => $user->driving_license->documentFile->uuid]
            );
        }

        return response()->json($user);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'surname' => 'string|max:255',
            'username' => 'required|string|max:255|unique:users,username,' . $user->id,
            'birthday' => 'date',
            'birthplace' => 'string|max:255',
            'birthplace_province' => 'string|max:255',
            'ssn' => 'string|max:255',
            'course_date' => 'date',
            'driver' => 'required|boolean',
            'chief' => 'required|boolean',
            'banned' => 'boolean',
            'hidden' => 'boolean',
            'driving_license' => 'array',
            'driving_license.number' => 'alpha_num|max:255',
            'driving_license.type' => 'string|max:255',
            'driving_license.expiration_date' => 'date',
            'driving_license.scan' => 'string|max:255',
            'address' => 'string|max:255',
            'address_zip_code' => 'integer|max:255',
            'phone_number' => 'string|max:255',
            'email' => 'string|max:255',
            'suit_size' => 'string|max:255',
            'boot_size' => 'string|max:255'
        ]);

        /*
        //TODO: new user permissions
        if($request->user()->isAbleTo("users-update")) {
            $user->update($request->all());
        } else {
            $user->update($request->except(['chief', 'driver', 'banned', 'hidden']));
        }
        */

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
            $drivingLicense = Document::where('added_by', $user->id)
                ->where('type', 'driving_license')
                ->first();

            if(is_null($drivingLicense)) {
                $drivingLicense = new Document();
                $drivingLicense->added_by = $user->id;
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

        return response()->json($user);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        //
    }
}
