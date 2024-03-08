<?php
namespace App\Http\Controllers\API;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{

    public function _validate($request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required',
            'password' => 'required',
            'device_info' => 'required'
        ]);

        return $validator;
    }

    public function login(Request $request)
    {
        try {
            $this->_validate($request);

            $message = 'Invalid Credential';
    
            $credentials = $request->only('username', 'password');

            if (Auth::attempt($credentials)) {

                $user = $request->user();

                if ($user->status == 'Active') {

                    $token = $request->user()->createToken($request->user()->id)->plainTextToken;
        
                    ActivityLogger::logActivity($request,"Success",200);
                    return response()->json([
                        'message' => true,
                        'status' => 200,
                        'response' => [
                            'token' => $token
                        ],
                    ], 200);

                } else {
                    ActivityLogger::logActivity($request, 'User status is not active'.' ( user = '.$request->username. ' & pass = '.$request->password.')', 403);
                    return response()->json([
                        'message' => 'User status is not active',
                        "status" => 403,
                        "response" => null
                    ], 403);
                }
            }
    
            ActivityLogger::logActivity($request,$message.' ( user = '.$request->username. ' & pass = '.$request->password.')',401);

            return response()->json([
                'message' => $message,
                "status" => 401,
                "response" => $request->all()
            ], 401);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred',
                'status' => 500,
                "response" => $e
            ], 500);
        }
    }

    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();

        ActivityLogger::logActivity($request,"Success",200);
        return response()->json([
            'message' => 'Logout successfully',
            'status' => 200,
            "response" => [
                "token_deleted"=> $request->bearerToken()
            ]
        ], 200);
    }
}
