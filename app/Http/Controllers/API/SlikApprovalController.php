<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\M_SlikApproval;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


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

    public function timeNow(){
        $formattedDateTime = Carbon::now()->format('Y-m-d H:i:s');
        
        return $formattedDateTime;
    }

    public function update(Request $request)
    {
        DB::beginTransaction();
        try {
            self::_validate($request);

            $check = M_SlikApproval::where('CR_PROSPECT_ID',$request->cr_prospect_id)->firstOrFail();

            if($request->filled('spv_employee_id')){
                $request->validate([
                    'spv_approval' => 'required|string'
                ]);

                $data_array =  [
                    'ONCHARGE_APPRVL' => $request->spv_approval,
                    'ONCHARGE_PERSON' => $request->spv_employee_id,
                    'ONCHARGE_DESCR' => $request->spv_description,
                    'ONCHARGE_TIME' => self::timeNow()
                ];

                $check->update($data_array);

            }else {
                $request->validate([
                    'deb_approval' => 'required|string'
                ]);

                $data_array =  [
                    'DEB_APPRVL'=> $request->deb_approval,
                    'DEB_DESCR' => $request->deb_description,
                    'DEB_TIME' => self::timeNow()
                ];
                
                $check->update($data_array);
            }
    
            DB::commit();
            ActivityLogger::logActivity($request,"Success",200);
            return response()->json(['message' => 'Slik Approval Updated successfully',"status" => 200], 200);
        } catch (ModelNotFoundException $e) {
            DB::rollback();
            ActivityLogger::logActivity($request,'Data Not Found',404);
            return response()->json(['message' => 'Data Not Found',"status" => 404], 404);
        } catch (\Exception $e) {
            DB::rollback();
            ActivityLogger::logActivity($request,$e->getMessage(),500);
            return response()->json(['message' => $e->getMessage(),"status" => 500], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
