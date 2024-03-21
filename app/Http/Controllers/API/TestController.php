<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class TestController extends Controller
{
    public function index (){

        $employees = DB::table('hr_employee as t0')
                        ->select('t2.id as user_id', 't2.username', 't2.email', 't0.id', 't0.NIK', 't0.NAMA', 't1.*')
                        ->leftJoin(DB::raw('(select a.NIK,
                                                    b.MASTER_NAME as bagian, 
                                                    c.MASTER_NAME as jabatan,
                                                    a.spv as spv_id,
                                                    d.NAMA as spv_name,
                                                    a.USE_FLAG
                                            from hr_rolling a 
                                                left join hr_division b 
                                                    on b.ID = a.BAGIAN
                                                left join hr_position c
                                                    on c.ID = a.JABATAN
                                                left join hr_employee d
                                                    on d.id = a.spv
                                            where a.USE_FLAG = "Active"
                                            order by a.NIK) as t1'), 't1.NIK', '=', 't0.NIK')
                        ->leftJoin('users as t2', function ($join) {
                            $join->on('t2.employee_id', '=', 't0.id')
                                ->where('t2.status', '=', 'Active');
                        })
                        ->where('t0.STATUS_MST', 'Active')
                        ->get();

        $searchId = 'c6fd09a5-4eab-11e9-b250-e0d55e0ad3ad'; 
        $employeesArray = $this->buildEmployeeHierarchy($employees, null, $searchId);
        
        return response()->json(['message' => 'Ok', "status" => 200, 'response' =>  $employeesArray], 200);

    }

    function buildEmployeeHierarchy($employees, $parentId = null, $searchId = null) {
        // $employeeTree = [];
        // foreach ($employees as $employee) {
        //     if ($employee->spv_id == $parentId) {
        //         $subEmployees = $this->buildEmployeeHierarchy($employees, $employee->id, $searchId);
        //         $employeeData = [
        //             "id" => $employee->id,
        //             "USER_ID" => $employee->user_id,
        //             "USERNAME" => $employee->username,
        //             "EMAIL" => $employee->email,
        //             "NIK" => $employee->NIK,
        //             "NAMA" => $employee->NAMA,
        //             "bagian" => $employee->bagian,
        //             "jabatan" => $employee->jabatan,
        //             "spv_id" => $employee->spv_id,
        //             "spv_name" => $employee->spv_name,
        //             "USE_FLAG" => $employee->USE_FLAG,
        //             "sub_list" => $subEmployees
        //         ];

        //         if ($employee->id == $searchId) {
        //             return $employeeData; 
        //         }

        //         $employeeTree[] = $employeeData;
        //     }
        // }
        // return $employeeTree;
    }
    
}
