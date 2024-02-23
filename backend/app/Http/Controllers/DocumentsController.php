<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\File;
use App\Models\Document;
use App\Models\DocumentFile;
use App\Utils\Logger;

class DocumentsController extends Controller
{
    /**
     * Upload driving license scan
     */
    public function uploadDrivingLicenseScan(Request $request)
    {
        $request->validate([
            'file' => [
                'required',
                File::image()
                    ->max(5 * 1024)
            ]
        ]);

        $fileName = time() . '_' . $request->file->getClientOriginalName();
        $filePath = $request->file('file')->storeAs('driving_license_scans', $fileName, 'public');

        $uuid = Str::uuid()->toString();

        $document = new DocumentFile();
        $document->uuid = $uuid;
        $document->type = 'driving_license';
        $document->file_path = $filePath;
        $document->uploadedBy()->associate(auth()->user());
        $document->save();

        Logger::log("Caricamento scansione patente", auth()->user()->id);

        return response()->json([
            "uuid" => $document->uuid
        ]);
    }

    /**
     * Serve driving license scan
     */
    public function serveDrivingLicenseScan($uuid)
    {
        $document = DocumentFile::where('uuid', $uuid)->firstOrFail();

        return response()->file(storage_path('app/public/' . $document->file_path));
    }

    /**
     * Add training course data to user
     */
    function addTrainingCourse(Request $request)
    {
        $request->validate([
            'user' => ['required', 'integer', 'exists:users,id'],
            'type' => ['required', 'integer', 'exists:training_course_types,id'],
            'date' => ['required', 'date'],
            'doc_number' => ['required', 'string', 'max:255'],
            'file' => [
                File::types('application/pdf')
                    ->max(50 * 1024)
            ]
        ]);

        if($request->user()->id != $request->input('user') && !$request->user()->hasPermission("users-add-training-course")) abort(401);
        if($request->user()->id == $request->input('user') && !$request->user()->hasPermission("user-add-training-course")) abort(401);

        $document = new Document();
        $document->type = 'training_course';
        $document->doc_type = $request->input('type');
        $document->doc_number = $request->input('doc_number');
        $document->user = $request->input('user');
        $document->added_by = auth()->user()->id;
        if($request->hasFile('file')) {
            $fileName = time() . '_' . $request->file->getClientOriginalName();
            $filePath = $request->file('file')->storeAs('training_courses', $fileName, 'public');

            $documentFile = new DocumentFile();
            $documentFile->uuid = Str::uuid()->toString();
            $documentFile->type = 'training_course';
            $documentFile->file_path = $filePath;
            $documentFile->uploadedBy()->associate(auth()->user());
            $documentFile->save();

            $document->documentFile()->associate($documentFile);
        }
        $document->date = $request->input('date');
        $document->save();

        Logger::log("Aggiunta corso di formazione", $request->input('user'));

        return response()->json([
            "id" => $document->id
        ]);
    }

    /**
     * Serve training course document
     */
    function serveTrainingCourse($uuid)
    {
        $document = DocumentFile::where('uuid', $uuid)->firstOrFail();

        return response()->file(storage_path('app/public/' . $document->file_path));
    }

    /**
     * Add medical examination data to user
     */
    function addMedicalExamination(Request $request)
    {
        $request->validate([
            'user' => ['required', 'integer', 'exists:users,id'],
            'date' => ['required', 'date'],
            'certifier' => ['required', 'string', 'max:255'],
            'expiration_date' => ['required', 'date'],
            'file' => [
                File::types('application/pdf')
                    ->max(50 * 1024)
            ]
        ]);

        if($request->user()->id != $request->input('user') && !$request->user()->hasPermission("users-add-medical-examination")) abort(401);
        if($request->user()->id == $request->input('user') && !$request->user()->hasPermission("user-add-medical-examination")) abort(401);

        $document = new Document();
        $document->type = 'medical_examination';
        $document->doc_certifier = $request->input('doctor');
        $document->user = $request->input('user');
        $document->added_by = auth()->user()->id;
        if($request->hasFile('file')) {
            $fileName = time() . '_' . $request->file->getClientOriginalName();
            $filePath = $request->file('file')->storeAs('medical_examinations', $fileName, 'public');

            $documentFile = new DocumentFile();
            $documentFile->uuid = Str::uuid()->toString();
            $documentFile->type = 'medical_examination';
            $documentFile->file_path = $filePath;
            $documentFile->uploadedBy()->associate(auth()->user());
            $documentFile->save();

            $document->documentFile()->associate($documentFile);
        }
        $document->date = $request->input('date');
        $document->expiration_date = $request->input('expiration_date');
        $document->save();

        Logger::log("Aggiunta visita medica", $request->input('user'));

        return response()->json([
            "id" => $document->id
        ]);
    }

    /**
     * Serve medical examination document
     */
    function serveMedicalExamination($uuid)
    {
        $document = DocumentFile::where('uuid', $uuid)->firstOrFail();

        return response()->file(storage_path('app/public/' . $document->file_path));
    }
}
