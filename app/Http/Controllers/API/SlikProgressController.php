<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\URL;

class SlikProgressController extends Controller
{
    public function index()
    {
        return response()->json(['progress_slik']);
    }

    public function creaturl($id)
    {
        //? set expired link here
        $exp_set = now()->addMinutes(1);

        $url = URL::temporarySignedRoute('approve_slik', $exp_set, ['id' => $id]);

        return response()->json([
            'url' => $url,
        ]);
    }

    public function approval_data()
    {
        //approval data here
        return response()->json(['approval_data']);
    }

    public function approve()
    {
        //approve data here
        return response()->json(['approve']);
    }
}
