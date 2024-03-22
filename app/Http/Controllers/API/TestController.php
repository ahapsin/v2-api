<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\M_HrEmployee;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;


class TestController extends Controller
{
    public function index (){

        // $userId =Auth::user()->employee_id;
        // $data = $this->getEmployeeHierarchy($userId);

        $test = $this->mapEmployeeToSupervisor();
        
        return response()->json(['message' => 'Ok', "status" => 200, 'response' =>  $test], 200);

    }

    function mapEmployeeToSupervisor() {

        $userId =Auth::user()->employee_id;
        $employees = M_HrEmployee::subOrdinateList($userId);

        $mappedEmployees = [];

        foreach ($employees as $employee) {

            $employeeId = $employee['employee_id'];
            $supervisorId = $employee['spv_id'];
    
            // Check if the supervisor is also an employee
            $supervisorEmployee = array_filter($employees, function ($emp) use ($supervisorId) {
                return $emp['employee_id'] === $supervisorId;
            });
    
            // If supervisor is found, add them to the 'subordinate' field of the supervisor
            if (!empty($supervisorEmployee)) {
                $supervisorEmployee = reset($supervisorEmployee);
                $supervisorEmployee['subordinate'][] = $employee;
            } else {
                // If supervisor is not found, add the employee directly to the result
                $mappedEmployees[] = $employee;
            }
        }
    
        return $mappedEmployees;
    }


    // function getEmployeeHierarchy($employeeId)
    // {
        
    //     $userDetail = M_HrEmployee::userDetail($employeeId);

    //     if ($userDetail->ID === $userDetail->spv_id) {
    //         $subordinates = M_HrEmployee::subOrdinateList($employeeId);

    //         foreach ($subordinates as $subordinate) {
    //             $userDetail['subordinates'][] = $this->getEmployeeHierarchy($subordinate->ID);
    //         }
    //     }

    //     return $userDetail;
    // }

    // function buildEmployeeHierarchy($employees, $parentId = null, $searchId = null) {
    //     $employeeTree = [];
    //     foreach ($employees as $employee) {
    //         if ($employee->spv_id == $parentId) {
    //             $subEmployees = $this->buildEmployeeHierarchy($employees, $employee->id, $searchId);
    //             $employeeData = [
    //                 "id" => $employee->id,
    //                 "user_id" => $employee->user_id,
    //                 "username" => $employee->username,
    //                 "email" => $employee->email,
    //                 "nik" => $employee->NIK,
    //                 "nama" => $employee->NAMA,
    //                 "bagian" => $employee->bagian,
    //                 "jabatan" => $employee->jabatan,
    //                 "spv_id" => $employee->spv_id,
    //                 "spv_name" => $employee->spv_name,
    //                 "USE_FLAG" => $employee->USE_FLAG,
    //                 "sub_list" => $subEmployees
    //             ];

    //             $employeeTree[] = $employeeData;
    //         }
    //     }
    //     return $employeeTree;
    // }
    
}
