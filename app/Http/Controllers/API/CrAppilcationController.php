<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\R_CrApplication;
use App\Models\CrApplication\M_CrApplication;
use Illuminate\Http\Request;

class CrAppilcationController extends Controller
{
    public function index(Request $request)
    {
        try {
            $data = self::showData();

            return response()->json(['message' => 'OK',"status" => 200,'response' => $data], 200);
        } catch (\Exception $e) {
            ActivityLogger::logActivity($request,$e->getMessage(),500);
            return response()->json(['message' => $e->getMessage(),"status" => 500], 500);
        }
    }

    private function showData(){
        $datas = [
            'permohonan_pembiayaan' => "",
            'data_pribadi_pemohon' => "",
            'data_pekerjaan_usaha' => "",
            'data_suami_istri' => "",
            'data_penjaminan' => "",
            'data_jaminan' => "",
            'informasi' => "",
            'data_referensi_keluarga' => ""
        ];

        return $datas;
    }
}
