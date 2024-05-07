<?php

namespace App\Http\Controllers\API\Menu;

use App\Http\Controllers\API\ActivityLogger;
use App\Http\Controllers\Controller;
use App\Models\M_MasterMenu;
use App\Models\M_MasterUserAccessMenu;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserAccessMenuController extends Controller
{
    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
        
            $request->validate([
                'master_menu_id' => [
                    'required',
                    'string',
                    function ($attribute, $value, $fail) use ($request) {
                        $exists = DB::table('master_users_access_menu')
                            ->where('users_id', $request->user()->id)
                            ->where('master_menu_id', $request->master_menu_id)
                            ->exists();
                            
                        if ($exists) {
                            $fail("The combination of $attribute and users_id already exists.");
                        }
                    },
                ],
            ]);

            M_MasterMenu::where('id',$request->master_menu_id)->firstOrFail();
            
            $validator = [
                'users_id' =>$request->user()->id,
                'master_menu_id' => $request->master_menu_id,
                'created_at' =>Carbon::now()->format('Y-m-d'),
                'created_by' => $request->user()->id
            ];
            
            M_MasterUserAccessMenu::create($validator);

            DB::commit();
            return response()->json(['message' => 'Users Access Menu created successfully', "status" => 200], 200);
        } catch (ModelNotFoundException $e) {
            DB::rollback();
            ActivityLogger::logActivity($request,'Data Not Found',404);
            return response()->json(['message' => 'Data Not Found',"status" => 404], 404);
        } catch (\Exception $e) {
            DB::rollback();
            ActivityLogger::logActivity($request,$e->getMessage(),500);
            return response()->json(['message' => $e->getMessage(), "status" => 500], 500);
        }
    }

}
