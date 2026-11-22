<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ExamProctorLog;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProctorController extends Controller
{
    public function uploadSnapshot(Request $request)
    {
        $request->validate([
            'exam_id' => 'required|integer',
            'image' => 'required|string', // Base64 image
        ]);

        // Proses decode Base64 image dari Webcam
        $imageParts = explode(";base64,", $request->image);
        $imageTypeAux = explode("image/", $imageParts[0]);
        $imageType = $imageTypeAux[1];
        $imageBase64 = base64_decode($imageParts[1]);

        // Buat nama file unik
        $fileName = 'proctor/' . auth()->id() . '_' . time() . '.' . $imageType;
        
        // Simpan ke storage (storage/app/public/proctor)
        Storage::disk('public')->put($fileName, $imageBase64);

        // Simpan ke database
        ExamProctorLog::create([
            'user_id' => auth()->id(),
            'exam_id' => $request->exam_id,
            'image_path' => $fileName,
        ]);

        return response()->json(['status' => 'success']);
    }
}