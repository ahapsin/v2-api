<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
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

    public static function subOrdinateList($employeeID){

        $subList = M_HrRolling::select('hr_rolling.NIK as nik', 'hr_employee.ID as employee_id', 'hr_employee.NAMA as nama')
                            ->leftJoin('hr_employee', 'hr_employee.NIK', '=', 'hr_rolling.NIK')
                            ->where('hr_rolling.USE_FLAG', 'Active')
                            ->where('hr_rolling.SPV', $employeeID)
                            ->where('hr_employee.STATUS_MST', 'Active')
                            ->get();

        return $subList;
        
    }

    public static function getSpv($nik){

        $setSpv = M_HrRolling::select('hr_employee.*')
                                ->leftJoin('hr_employee', 'hr_employee.ID', '=', 'hr_rolling.SPV')
                                ->where('hr_rolling.USE_FLAG', '=', 'Active')
                                ->where('hr_rolling.NIK', '=', $nik)
                                ->get();

        return $setSpv;
    }
}
