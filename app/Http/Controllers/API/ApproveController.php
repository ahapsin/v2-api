<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;

class ApproveController extends Controller
{
    public function generate()
    {
        // $url = URL::temporarySignedRoute('approve.show', now()->addMinutes(120), ['id' => 123]);

        $url = URL::signedRoute('approveValid',[
            'data' => 123
        ]);

        return response()->json(['signed_url' => $url], 200);
    }

    public function index($data){

        // if (!request()->hasValidSignature()) {
        //     return abort(404);
        // }

        return response()->json(['message' => 'Ok', "status" => 200, 'response' =>  $data], 200);
    }
}
