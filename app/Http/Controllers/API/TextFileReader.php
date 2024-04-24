<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class TextFileReader extends Controller
{

    public function uploadText(Request $req)
    {
        try {
            DB::beginTransaction();

            $getFile = file_get_contents($req->txt_file);
            $fileContents = mb_convert_encoding($getFile, 'UTF-8', 'ISO-8859-1');
            $convert = json_decode($fileContents);

            DB::commit();
            return response()->json(['message' => 'File upload successfully',"status" => 200,'response' =>$convert], 200);
        } catch (\Exception $e) {
            DB::rollback();
            ActivityLogger::logActivity($req,$e->getMessage(),500);
            return response()->json(['message' => $e->getMessage(),"status" => 500], 500);
        } 
    }

}
