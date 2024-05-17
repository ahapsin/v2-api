<?php

namespace App\Http\Controllers\API\Menu;

use App\Http\Controllers\API\ActivityLogger;
use App\Http\Controllers\Controller;
use App\Http\Resources\R_MasterMenu;
use Illuminate\Http\Request;
use App\Models\M_MasterMenu;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class MasterMenuController extends Controller
{

    public function index(Request $req)
    {
        try {
            $data = M_MasterMenu::orderBy('order', 'asc')
                                ->where(function($query) {
                                    $query->whereNull('deleted_by')
                                        ->orWhere('deleted_by', '');
                                })
                                ->get();

            $dto = R_MasterMenu::collection($data);

            ActivityLogger::logActivity($req,"Success",200);
            return response()->json(['message' => 'OK', "status" => 200, 'response' => $dto], 200);
        } catch (\Exception $e) {
            ActivityLogger::logActivity($req,$e->getMessage(),500);
            return response()->json(['message' => $e->getMessage(),"status" => 500], 500);
        }
    }

    public function subMenuList(Request $req)
    {
        try {
            $data = M_MasterMenu::orderBy('order', 'asc')
                                ->where(function($query) {
                                    $query->whereNull('deleted_by')
                                        ->orWhere('deleted_by', '');
                                })
                                ->where('parent', '!=', 0)
                                ->get();

            $dto = R_MasterMenu::collection($data);
            return response()->json(['message' => 'OK', "status" => 200, 'response' => $dto], 200);
        } catch (\Exception $e) {
            ActivityLogger::logActivity($req,$e->getMessage(),500);
            return response()->json(['message' => $e->getMessage(),"status" => 500], 500);
        }
    }

    public function show(Request $req,$id)
    {
        try {
            $check = M_MasterMenu::where('id',$id)
                                ->where(function($query) {
                                    $query->whereNull('deleted_by')
                                        ->orWhere('deleted_by', '');
                                    })->firstOrFail();

            $dto = R_MasterMenu::collection([$check]);

            // ActivityLogger::logActivity($req,"Success",200);
            return response()->json(['message' => 'OK',"status" => 200,'response' => $dto], 200);
        } catch (ModelNotFoundException $e) {
            // ActivityLogger::logActivity($req,'Data Not Found',404);
            return response()->json(['message' => 'Data Not Found',"status" => 404], 404);
        } catch (\Exception $e) {
            // ActivityLogger::logActivity($req,$e->getMessage(),500);
            return response()->json(['message' => $e->getMessage(),"status" => 500], 500);
        }
    }

    public function store(Request $req)
    { 
        DB::beginTransaction();
        try {
            $req->validate([
                'menu_name' => 'required|string',
                'route' => 'required|string',
                'order' => 'numeric',
                'leading' => 'string'
            ]);

            $req['status'] = 'Active';
            $req['created_by'] = $req->user()->id;

            if($req->parent == ""){
                $req['parent'] = 0;
            }else{
                $req['parent'] = $req->parent;
            }

            $create =  M_MasterMenu::create($req->all());
            DB::commit();
            // ActivityLogger::logActivity($req,"Success",200);
            
            return response()->json(['message' => 'Master Menu created successfully', "status" => 200, 'response' => $create], 200);
        } catch (QueryException $e) {
            DB::rollback();
            // ActivityLogger::logActivity($req,$e->getMessage(),409);
            return response()->json(['message' => $e->getMessage(), "status" => 409], 409);
        } catch (\Exception $e) {
            DB::rollback();
            // ActivityLogger::logActivity($req,$e->getMessage(),500);
            return response()->json(['message' => $e->getMessage(), "status" => 500], 500);
        }
    }

    public function update(Request $req,$id)
    { 
        DB::beginTransaction();
        try {
            $menu = M_MasterMenu::findOrFail($id);

            $req['updated_by'] = $req->user()->id;
            $req['updated_at'] = Carbon::now()->format('Y-m-d H:i:s');

            $menu->update($req->all());
            DB::commit();
            // ActivityLogger::logActivity($req,"Success",200);
            return response()->json(['message' => 'Master Menu updated successfully', "status" => 200], 200);
        } catch (ModelNotFoundException $e) {
            DB::rollback();
            // ActivityLogger::logActivity($req,'Data Not Found',404);
            return response()->json(['message' => 'Data Not Found', "status" => 404], 404);
        } catch (\Exception $e) {
            DB::rollback();
            // ActivityLogger::logActivity($req,$e->getMessage(),500);
            return response()->json(['message' => $e->getMessage(), "status" => 500], 500);
        }
    }

    public function destroy(Request $req,$id)
    { 
        DB::beginTransaction();
        try {
            
            $menu = M_MasterMenu::findOrFail($id);

            $update = [
                'deleted_by' =>$req->user()->id,
                'deleted_at' =>  Carbon::now()->format('Y-m-d H:i:s')
            ];

            $menu->update($update);
            
            DB::commit();
            // ActivityLogger::logActivity($req,"Success",200);
            return response()->json(['message' => 'Master Menu deleted successfully', "status" => 200], 200);
        } catch (ModelNotFoundException $e) {
            DB::rollback();
            // ActivityLogger::logActivity($req,'Data Not Found',404);
            return response()->json(['message' => 'Data Not Found', "status" => 404], 404);
        } catch (\Exception $e) {
            DB::rollback();
            // ActivityLogger::logActivity($req,$e->getMessage(),500);
            return response()->json(['message' => $e->getMessage(), "status" => 500], 500);
        }
    }
    // menuSubListTesting
    public function menuSubList(Request $req)
    {
        try {
            $data = M_MasterMenu::orderBy('order', 'asc')->where('status','active')->whereNull('deleted_by')->get();
        
            return response()->json(['message' => 'OK', "status" => 200, 'response' => M_MasterMenu::buildMenuArray($data)], 200);
        } catch (\Exception $e) {
            // ActivityLogger::logActivity($req,$e->getMessage(),500);
            return response()->json(['message' => $e->getMessage(),"status" => 500], 500);
        }
    }

    public function menuSubListTesting(Request $req)
    {
        try {
                $userId = $req->user()->id;

                $directAccessQuery = DB::table('users as t1')
                    ->leftJoin('master_users_access_menu as t2', 't2.users_id', '=', 't1.id')
                    ->leftJoin('master_menu as t3', 't3.id', '=', 't2.master_menu_id')
                    ->where('t1.id', $userId)
                    ->select('t3.*');
                
                $groupAccessQuery = DB::table('master_users_access_group as a')
                    ->leftJoin('master_group_access_menu as b', 'b.master_group_id', '=', 'a.group_id')
                    ->leftJoin('master_menu as c', 'c.id', '=', 'b.master_menu_id')
                    ->where('a.users_id', $userId)
                    ->select('c.*');
                
                $results = $directAccessQuery->union($groupAccessQuery)->get();
            
        
            return response()->json(['message' => 'OK', "status" => 200, 'response' => M_MasterMenu::buildMenuArrayTest($results)], 200);
        } catch (\Exception $e) {
            ActivityLogger::logActivity($req,$e->getMessage(),500);
            return response()->json(['message' => $e->getMessage(),"status" => 500], 500);
        }
    }
}
