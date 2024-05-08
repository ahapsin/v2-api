<?php

namespace App\Http\Controllers\API\Menu;

use App\Http\Controllers\API\ActivityLogger;
use App\Http\Controllers\Controller;
use App\Models\M_MasterMenu;
use App\Models\M_MasterRoleAccessMenu;
use App\Models\M_Role;
use App\Rules\NumericZeroOne;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Database\RecordsNotFoundException;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class RoleAccessMenuController extends Controller
{
    public function index(Request $request)
    {
        try {
            $data = M_MasterRoleAccessMenu::all();
        
            ActivityLogger::logActivity($request,"Success",200);
            return response()->json(['message' => 'OK', "status" => 200, 'response' => $data], 200);
        } catch (\Exception $e) {
            ActivityLogger::logActivity($request,$e->getMessage(),500);
            return response()->json(['message' => $e->getMessage(),"status" => 500], 500);
        }
    }

    // public function show(Request $req,$id)
    // {
    //     try {
    //         $check = M_Role::where('id',$id)->firstOrFail();

    //         ActivityLogger::logActivity($req,"Success",200);
    //         return response()->json(['message' => 'OK',"status" => 200,'response' => $check], 200);
    //     } catch (ModelNotFoundException $e) {
    //         ActivityLogger::logActivity($req,'Data Not Found',404);
    //         return response()->json(['message' => 'Data Not Found',"status" => 404], 404);
    //     } catch (\Exception $e) {
    //         ActivityLogger::logActivity($req,$e->getMessage(),500);
    //         return response()->json(['message' => $e->getMessage(),"status" => 500], 500);
    //     }
    // }

    private function validateNumericZeroOne($attribute, $value, $fail)
    {
        if (!is_numeric($value) || ($value != 0 && $value != 1)) {
            $fail('The '.$attribute.' must be numeric and either 0 or 1.');
        }
    }

    private function __validator($request){
        $validator = Validator::make($request->all(), [
            'master_menu_id' => 'required|string',
            'master_role_id' => [
                'required',
                'string',
                function ($attribute, $value, $fail) use ($request) {
                    $exists = DB::table('master_role_access_menu')
                        ->where('master_role_id', $value)
                        ->where('master_menu_id', $request->master_menu_id)
                        ->exists();
                        
                    if ($exists) {
                        $fail("The combination of $attribute and master_menu_id already exists.");
                    }
                },
            ]
        ]);

        return $validator;
    }

    public function store(Request $request)
    {
        DB::beginTransaction();
        try {

            $validator = self::__validator($request);

            if ($validator->fails()) {
                $errorMessage = implode(', ', $validator->errors()->all());
                throw new Exception($errorMessage,500);
            }

            $masterMenu = M_MasterMenu::where('id',$request->master_menu_id)->first();

            if(empty($masterMenu)){   
                throw new Exception("Data Master Menu Not Found", 404);
            }

            $role = M_Role::where('id',$request->master_role_id)->first();

            if(empty($role)){   
                throw new Exception("Data Role Not Found", 404);
            }

            $validator = [
                'master_menu_id' => $request->master_menu_id,
                'master_role_id' => $request->master_role_id,
                "view" => $request->view,
                "insert" => $request->insert,
                "update" => $request->update,
                "delete" => $request->delete,
                'created_at' =>Carbon::now()->format('Y-m-d'),
                'created_by' =>$request->user()->id
            ];
            
            M_MasterRoleAccessMenu::create($validator);

            DB::commit();
            return response()->json(['message' => 'Role Access Menu created successfully', "status" => 200], 200);
        } catch (\Exception $e) {
            DB::rollback();
            ActivityLogger::logActivity($request,$e->getMessage(),$e->getCode());
            return response()->json(['message' => $e->getMessage(), "status" => $e->getCode()], $e->getCode());
        }
    }

    // public function update(Request $request,$id)
    // {
    //     DB::beginTransaction();
    //     try {
    //         $request->validate([
    //             'role_name' => 'unique:master_role,role_name,'.$id,
    //         ]);

    //         $role = M_Role::findOrFail($id);

    //         $validator = [
    //             'role_name' => $request->input('role_name'),
    //             'status' => $request->input('status'),
    //             'updated_at' =>Carbon::now()->format('Y-m-d'),
    //             'updated_by' =>$request->user()->id
    //         ];

    //         $role->update($validator);

    //         DB::commit();
    //         ActivityLogger::logActivity($request,"Success",200);
    //         return response()->json(['message' => 'Master Role updated successfully', "status" => 200], 200);
    //     } catch (ModelNotFoundException $e) {
    //         DB::rollback();
    //         ActivityLogger::logActivity($request,'Data Not Found',404);
    //         return response()->json(['message' => 'Data Not Found', "status" => 404], 404);
    //     } catch (\Exception $e) {
    //         DB::rollback();
    //         ActivityLogger::logActivity($request,$e->getMessage(),500);
    //         return response()->json(['message' => $e->getMessage(), "status" => 500], 500);
    //     }
    // }

    // public function destroy(Request $req,$id)
    // { 
    //     DB::beginTransaction();
    //     try {
            
    //         $role = M_Role::findOrFail($id);

    //         $update = [
    //             'deleted_by' => $req->user()->id,
    //             'deleted_at' => Carbon::now()->format('Y-m-d H:i:s')
    //         ];

    //         $role->update($update);

    //         DB::commit();
    //         ActivityLogger::logActivity($req,"Success",200);
    //         return response()->json(['message' => 'Master Role deleted successfully', "status" => 200], 200);
    //     } catch (ModelNotFoundException $e) {
    //         DB::rollback();
    //         ActivityLogger::logActivity($req,'Data Not Found',404);
    //         return response()->json(['message' => 'Data Not Found', "status" => 404], 404);
    //     } catch (\Exception $e) {
    //         DB::rollback();
    //         ActivityLogger::logActivity($req,$e->getMessage(),500);
    //         return response()->json(['message' => $e->getMessage(), "status" => 500], 500);
    //     }
    // }
}
