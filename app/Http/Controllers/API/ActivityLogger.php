<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Ramsey\Uuid\Uuid;
use App\Models\M_ActivityLogger;
use App\Models\M_ActivityLoggerLogin;

class ActivityLogger extends Controller
{
    public static function logActivity(Request $request,$msg,$status_code)
    {
        $log = new M_ActivityLogger();
        $log->id = Uuid::uuid4()->toString();
        $log->user_id = isset($request->user()->id)?$request->user()->id:$request->username;
        $log->method = $request->method();
        $log->status = $status_code;
        $log->url_api = $request->fullUrl();
        $log->activity_description = $msg;
        $log->device_info = isset($request->device_info)?$request->device_info:"";
        $log->ip_address = $request->ip();
        $log->user_agent = $request->header('User-Agent');
        $log->save();
    }

    public static function logActivityLogin(Request $request,$event,$msg,$status_code)
    {
        $log = new M_ActivityLoggerLogin();
        $log->id = Uuid::uuid4()->toString();
        $log->user = $request->username;
        $log->event = $event;
        $log->status = $status_code;
        $log->url_api = $request->fullUrl();
        $log->activity_description = $msg;
        $log->device_info = isset($request->device_info)?$request->device_info:"";
        $log->ip_address = $request->ip();
        $log->browser = $request->header('User-Agent');
        $log->save();
    }

    public static function logActivityLogout(Request $request,$event,$msg,$status_code,$username)
    {
        $log = new M_ActivityLoggerLogin();
        $log->id = Uuid::uuid4()->toString();
        $log->user = $username;
        $log->event = $event;
        $log->status = $status_code;
        $log->url_api = $request->fullUrl();
        $log->activity_description = $msg;
        $log->device_info = isset($request->device_info)?$request->device_info:"";
        $log->ip_address = $request->ip();
        $log->browser = $request->header('User-Agent');
        $log->save();
    }
}
