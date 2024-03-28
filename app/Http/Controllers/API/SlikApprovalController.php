<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\M_SlikApproval;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Uuid;

class SlikApprovalController extends Controller
{
   
    public function index(Request $request)
    {
        try {
            $data = M_SlikApproval::all();

            return response()->json(['message' => 'OK',"status" => 200,'response' => $data], 200);
        } catch (QueryException $e) {
            ActivityLogger::logActivity($request,$e->getMessage(),409);
            return response()->json(['message' => $e->getMessage(),"status" => 409], 409);
        } catch (\Exception $e) {
            ActivityLogger::logActivity($request,$e->getMessage(),500);
            return response()->json(['message' => $e->getMessage(),"status" => 500], 500);
        }
    }

    public function _validate($request)
    {
        $validator = $request->validate([
            'cr_prospect_id' => 'required|string'
        ]);

        return $validator;
    }

    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            self::_validate($request);

            $data_array = [
                'ID' => Uuid::uuid4()->toString(),
                'CR_PROSPECT_ID' => $request->id,
                'ONCHARGE_APPRVL',
                'ONCHARGE_PERSON',
                'ONCHARGE_TIME',
                'ONCHARGE_DESCR',
                'DEB_APPRVL',
                'DEB_DESCR',
                'DEB_TIME',
                'SLIK_RESULT'
            ];
        
            return M_SlikApproval::create($data_array);
    
            DB::commit();
            ActivityLogger::logActivity($request,"Success",200);
            return response()->json(['message' => 'Kunjungan created successfully',"status" => 200,'response' => $request->all()], 200);
        } catch (QueryException $e) {
            DB::rollback();
            ActivityLogger::logActivity($request,$e->getMessage(),409);
            return response()->json(['message' => $e->getMessage(),"status" => 409], 409);
        } catch (\Exception $e) {
            DB::rollback();
            ActivityLogger::logActivity($request,$e->getMessage(),500);
            return response()->json(['message' => $e->getMessage(),"status" => 500], 500);
        }
    }

    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
