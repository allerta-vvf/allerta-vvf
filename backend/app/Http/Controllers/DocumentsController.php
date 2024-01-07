<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\File;
use App\Models\DocumentFile;

class DocumentsController extends Controller
{
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

        return response()->json([
            "uuid" => $document->uuid
        ]);
    }

    public function serveDrivingLicenseScan($uuid)
    {
        //TODO: check if the user has access to the document
        $document = DocumentFile::where('uuid', $uuid)->firstOrFail();

        return response()->file(storage_path('app/public/' . $document->file_path));
    }
}
