<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\M_Group;
use App\Models\M_MasterMenu;
use App\Models\M_MasterUserAccessGroup;
use App\Models\M_MasterUserAccessMenu;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UsersAccessMenu extends Controller
{
    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
          
            $request->validate([
                'user_id' => 'required|string',
            ]);

            $check_user = User::where('id',$request->user_id)->get();

            if ($check_user->isEmpty()) {
                throw new Exception("User Id Not Found",404);
            }

            if ($request->has('list_menu_id') && is_array($request->list_menu_id)) {
                foreach ($request->list_menu_id as $value) {

                    $menuId = $value['menu_id'];

                    $check_menu = M_MasterMenu::where('id',$menuId)->get();

                    if ($check_menu->isEmpty()) {
                        throw new Exception("Menu Id Not Found",404);
                    }

                    $data_id_menu = [
                        'master_menu_id' => $menuId,
                        'users_id' => $request->user_id,
                        'created_at' => Carbon::now()->format('Y-m-d'),
                        'created_by' => $request->user()->id
                    ];
                
                    M_MasterUserAccessMenu::create($data_id_menu);
                }
            }

            if ($request->has('list_group_id') && is_array($request->list_group_id)) {
                foreach ($request->list_group_id as $value) {

                    $groupId = $value['group_id'];

                    $check_menu = M_Group::where('id',$groupId)->get();

                    if ($check_menu->isEmpty()) {
                        throw new Exception("Group Id Not Found",404);
                    }

                    $data_id_group = [
                        'group_id' => $groupId,
                        'users_id' => $request->user_id,
                        'created_at' => Carbon::now()->format('Y-m-d'),
                        'created_by' => $request->user()->id
                    ];
                
                    M_MasterUserAccessGroup::create($data_id_group);
                }
            }

            DB::commit();
            return response()->json(['message' => 'User Access Menu created successfully', "status" => 200], 200);
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
}
