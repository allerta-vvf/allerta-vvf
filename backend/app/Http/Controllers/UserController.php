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
        if($request->user()->id != $user->id && !$request->user()->hasPermission("users-read")) abort(401);

        User::where('id', $request->user()->id)->update(['last_access' => now()]);

        $dl_tmp = Document::where('documents.user', $user->id)
            ->where('documents.type', 'driving_license')
            ->join('document_files', 'document_files.id', '=', 'documents.document_file_id')
            ->select('documents.doc_type', 'documents.doc_number', 'documents.expiration_date', 'document_files.uuid as scan_uuid')
            ->get();
        
        if($dl_tmp->count() > 0) {
            $user->driving_license = $dl_tmp[0];
        }

        $me_tmp = Document::where('documents.user', $user->id)
            ->where('documents.type', 'medical_examination')
            ->leftJoin('document_files', 'document_files.id', '=', 'documents.document_file_id')
            ->select('documents.doc_certifier as certifier', 'documents.date', 'documents.expiration_date', 'document_files.uuid as cert_uuid')
            ->get();
        
        if($me_tmp->count() > 0) {
            $user->medical_examinations = $me_tmp;
            foreach($user->medical_examinations as $me) {
                if(!is_null($me->cert_uuid)) {
                    $me->cert_url = URL::temporarySignedRoute(
                        'medical_examination_serve', now()->addHours(1), ['uuid' => $me->cert_uuid]
                    );
                    unset($me->cert_uuid);
                }
            }
        } else {
            $user->medical_examinations = [];
        }

        if(!is_null($user->driving_license) && !is_null($user->driving_license->scan_uuid)) {
            $user->driving_license->scan_url = URL::temporarySignedRoute(
                'driving_license_scan_serve', now()->addMinutes(2), ['uuid' => $user->driving_license->scan_uuid]
            );
        }

        return response()->json($user);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        if($request->user()->id != $user->id && !$request->user()->hasPermission("users-update")) abort(401);
        if($request->user()->id == $user->id && !$request->user()->hasPermission("user-update")) abort(401);

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
