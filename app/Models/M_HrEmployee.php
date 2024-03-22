<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class M_HrEmployee extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $table = 'hr_employee';
    protected $fillable = [
        'ID',
        'NIK',
        'NAMA',
        'AO_CODE',
        'BLOOD_TYPE',
        'GENDER',
        'PENDIDIKAN',
        'UNIVERSITAS',
        'JURUSAN',
        'IPK',
        'IBU_KANDUNG',
        'STATUS_KARYAWAN',
        'NAMA_PASANGAN',
        'TANGGUNGAN',
        'NO_KTP',
        'NAMA_KTP',
        'ALAMAT_KTP',
        'SECTOR_KTP',
        'DISTRICT_KTP',
        'SUB_DISTRICT_KTP',
        'ALAMAT_TINGGAL',
        'SECTOR_TINGGAL',
        'DISTRICT_TINGGAL',
        'SUB_DISTRICT_TINGGAL',
        'TGL_LAHIR',
        'TEMPAT_LAHIR',
        'AGAMA',
        'TELP',
        'HP',
        'NO_REK_CF',
        'NO_REK_TF',
        'EMAIL',
        'NPWP',
        'SUMBER_LOKER',
        'KET_LOKER',
        'INTERVIEW',
        'TGL_KELUAR',
        'ALASAN_KELUAR',
        'CUTI',
        'PHOTO_LOC',
        'SPV',
        'STATUS_MST'
    ];
    
    protected $guarded = [];
    public $incrementing = false;
    protected $keyType = 'string';
    protected $primaryKey = 'ID';
    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if ($model->getKey() == null) {
                $model->setAttribute($model->getKeyName(), Str::uuid()->toString());
            }
        });
    }

    public static function userDetail($userId){

        $users = User::select('users.id as user_id', 'users.username', 'users.email', 
                                'users.status as status_user', 
                                'hr_employee.*', 
                                'hr_division.MASTER_NAME as bagian', 
                                'hr_position.MASTER_NAME as jabatan',
                                'spv.NAMA as spv_name')
                        ->leftJoin('hr_employee', 'hr_employee.id', '=', 'users.employee_id')
                        ->leftJoin('hr_rolling', function($join) {
                            $join->on('hr_rolling.NIK', '=', 'hr_employee.NIK')
                                ->where('hr_rolling.USE_FLAG', '=', 'Active');
                        })
                        ->leftJoin('hr_division', 'hr_division.ID', '=', 'hr_rolling.BAGIAN')
                        ->leftJoin('hr_position', 'hr_position.ID', '=', 'hr_rolling.JABATAN')
                        ->leftJoin('hr_employee as spv', 'spv.ID', '=', 'hr_rolling.SPV')
                        ->where('users.status', 'Active')
                        ->where('hr_employee.STATUS_MST', 'Active')
                        ->where('hr_employee.ID', $userId)
                        ->first();

        return $users;
    }

    public static function subOrdinateList($employeeID){
        $subList = M_HrRolling::select('hr_rolling.NIK', 'hr_employee.ID as employee_id', 'hr_employee.NAMA', 'hr_rolling.spv as spv_id', 'hr_rolling.USE_FLAG')
                            ->leftJoin('hr_employee', 'hr_employee.NIK', '=', 'hr_rolling.NIK')
                            ->where('hr_rolling.USE_FLAG', 'Active')
                            ->where('hr_rolling.SPV', $employeeID)
                            ->get();

        return $subList;
        
    }
}
