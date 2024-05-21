<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\M_HrPosition;
use Illuminate\Http\Request;

class HrPosition extends Controller
{
    public function index(Request $request)
    {
        try {
            $data =  M_HrPosition::all();

            ActivityLogger::logActivity($request,"Success",200);
            return response()->json(['message' => 'OK',"status" => 200,'response' => $data], 200);
        } catch (\Exception $e) {
            ActivityLogger::logActivity($request,$e->getMessage(),500);
            return response()->json(['message' => $e->getMessage(),"status" => 500], 500);
        }
    }
}
