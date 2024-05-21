<?php

namespace App\Http\Controllers\API\Menu;

use App\Http\Controllers\API\ActivityLogger;
use App\Http\Controllers\Controller;
use App\Http\Resources\R_MasterGroup;
use App\Models\M_Group;
use App\Models\M_HrPosition;
use App\Models\M_MasterGroupAccessMenu;
use App\Models\M_MasterMenu;
use App\Models\M_MasterPositionAccessGroup;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MasterGroupController extends Controller
{
    public function index(Request $request)
    {
        try {
            $data = M_Group::all();
            $dto = R_MasterGroup::collection($data);
        
            ActivityLogger::logActivity($request,"Success",200);
            return response()->json(['message' => 'OK', "status" => 200, 'response' => $dto], 200);
        } catch (\Exception $e) {
            ActivityLogger::logActivity($request,$e->getMessage(),500);
            return response()->json(['message' => $e->getMessage(),"status" => 500], 500);
        }
    }

    public function show(Request $req,$id)
    {
        try {
            $check = M_Group::where('id',$id)->firstOrFail();

            ActivityLogger::logActivity($req,"Success",200);
            return response()->json(['message' => 'OK',"status" => 200,'response' => $check], 200);
        } catch (ModelNotFoundException $e) {
            ActivityLogger::logActivity($req,'Data Not Found',404);
            return response()->json(['message' => 'Data Not Found',"status" => 404], 404);
        } catch (\Exception $e) {
            ActivityLogger::logActivity($req,$e->getMessage(),500);
            return response()->json(['message' => $e->getMessage(),"status" => 500], 500);
        }
    }

    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
          
            $request->validate([
                'group_name' => 'required|string|unique:master_group',
            ]);

            $validator = [
                'group_name' => $request->group_name,
                'status' => 'active',
                'created_at' => Carbon::now()->format('Y-m-d'),
                'created_by' => $request->user()->id
            ];
            
            $last_id_group = M_Group::create($validator);

            if ($request->has('position_id') && !empty($request->position_id)) {

                $check_position = M_HrPosition::where('ID',$request->position_id)->get();

                if ($check_position->isEmpty()) {
                    throw new Exception("Position Id Not Found",404);
                }

                $data_id_menu = [
                    'position_id' => $request->position_id,
                    'group_id' => $last_id_group->id,
                    'created_at' => Carbon::now()->format('Y-m-d'),
                    'created_by' => $request->user()->id
                    ];
                
                M_MasterPositionAccessGroup::create($data_id_menu);
            }

            if ($request->has('list_menu_id') && is_array($request->list_menu_id)) {
                foreach ($request->list_menu_id as $value) {

                    $menuId= $value['menu_id'];

                    $check_menu = M_MasterMenu::where('id',$menuId)->get();

                    if ($check_menu->isEmpty()) {
                        throw new Exception("Menu Id Not Found",404);
                    }

                    $data_id_menu = [
                        'master_menu_id' => $menuId,
                        'master_group_id' => $last_id_group->id,
                        'created_at' => Carbon::now()->format('Y-m-d'),
                        'created_by' => $request->user()->id
                    ];
                
                    M_MasterGroupAccessMenu::create($data_id_menu);
                }
            }

            DB::commit();
            return response()->json(['message' => 'Master Group created successfully', "status" => 200], 200);
        }catch (QueryException $e) {
            DB::rollback();
            ActivityLogger::logActivity($request,$e->getMessage(),409);
            return response()->json(['message' => $e->getMessage(), "status" => 409], 409);
        } catch (\Exception $e) {
            DB::rollback();
            ActivityLogger::logActivity($request,$e->getMessage(),500);
            return response()->json(['message' => $e->getMessage(), "status" => 500], 500);
        }
    }

    public function update(Request $request,$id)
    {
        DB::beginTransaction();
        try {
            $request->validate([
                'group_name' => 'unique:master_group,group_name,'.$id,
            ]);

            $role = M_Group::findOrFail($id);

            $validator = [
                'role_name' => $request->input('role_name'),
                'status' => $request->input('status'),
                'updated_at' =>Carbon::now()->format('Y-m-d'),
                'updated_by' =>$request->user()->id
            ];

            $role->update($validator);

            DB::commit();
            ActivityLogger::logActivity($request,"Success",200);
            return response()->json(['message' => 'Master Role updated successfully', "status" => 200], 200);
        } catch (ModelNotFoundException $e) {
            DB::rollback();
            ActivityLogger::logActivity($request,'Data Not Found',404);
            return response()->json(['message' => 'Data Not Found', "status" => 404], 404);
        } catch (\Exception $e) {
            DB::rollback();
            ActivityLogger::logActivity($request,$e->getMessage(),500);
            return response()->json(['message' => $e->getMessage(), "status" => 500], 500);
        }
    }

    public function destroy(Request $req,$id)
    { 
        DB::beginTransaction();
        try {
            
            $role = M_Group::findOrFail($id);

            $update = [
                'deleted_by' => $req->user()->id,
                'deleted_at' => Carbon::now()->format('Y-m-d H:i:s')
            ];

            $role->update($update);

            DB::commit();
            ActivityLogger::logActivity($req,"Success",200);
            return response()->json(['message' => 'Master Role deleted successfully', "status" => 200], 200);
        } catch (ModelNotFoundException $e) {
            DB::rollback();
            ActivityLogger::logActivity($req,'Data Not Found',404);
            return response()->json(['message' => 'Data Not Found', "status" => 404], 404);
        } catch (\Exception $e) {
            DB::rollback();
            ActivityLogger::logActivity($req,$e->getMessage(),500);
            return response()->json(['message' => $e->getMessage(), "status" => 500], 500);
        }
    }
}
