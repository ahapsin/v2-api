<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UsersController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    public function _validate($request)
    {

        $validator = $request->validate([
            'username' => 'required|string',
            'email' => 'required|email|unique:users',
            'password' => 'required|string'
        ]);

        return $validator;
    }


    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            self::_validate($request);

            $data_array = [
                'username' => $request->username,
                'email' => $request->email,
                'password' => bcrypt($request->password),
                'status' => $request->status,
            ];
        
            User::create($data_array);
    
            DB::commit();
            ActivityLogger::logActivity($request,"Success",200);
            return response()->json(['message' => 'User created successfully',"status" => 200], 200);
        }catch (QueryException $e) {
            DB::rollback();
            ActivityLogger::logActivity($request,$e->getMessage(),409);
            return response()->json(['message' => $e->getMessage(),"status" => 409], 409);
        } catch (\Exception $e) {
            DB::rollback();
            ActivityLogger::logActivity($request,$e->getMessage(),500);
            return response()->json(['message' => $e->getMessage(),"status" => 500], 500);
        }
    }

    public function update(Request $request)
    {
        DB::beginTransaction();
        try {
            
            $request->validate([
                'id' => 'required|string'
            ]);

            $check = User::findOrFail($request->id);

            $data_array =  [
                'username' =>  $request->username,
                'password' => Hash::make($request->password)
            ];

            $check->update($data_array);

            DB::commit();
            return response()->json(['message' => 'Updated Success',"status" => 200], 200);
        } catch (ModelNotFoundException $e) {
            DB::rollback();
            // ActivityLogger::logActivity($request,'Data Not Found',404);
            return response()->json(['message' => 'Data Not Found',"status" => 404], 404);
        } catch (\Exception $e) {
            DB::rollback();
            // ActivityLogger::logActivity($request,$e->getMessage(),500);
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
