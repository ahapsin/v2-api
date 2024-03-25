<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\M_LogSendOut;
use Illuminate\Http\Request;

class LogSendOutController extends Controller
{
    public function index(Request $request)
    {
        try {
            $data = M_LogSendOut::all();
        
            return response()->json(['message' => 'OK', "status" => 200, 'response' => $data], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage(),"status" => 500], 500);
        } 
    }
}
