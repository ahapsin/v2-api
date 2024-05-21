<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class M_HrPosition extends Model
{
    use HasFactory;
    protected $table = 'hr_position';

    protected $fillable = [
        'ID',
        'MASTER_NAME',
        'UPPER_LEVEL',
        'DESCRIPTION'
    ];

    protected $guarded = [];
    public $incrementing = false;
    protected $keyType = 'string';
    protected $primaryKey = 'ID';
    public $timestamps = false;
    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if ($model->getKey() == null) {
                $model->setAttribute($model->getKeyName(), Str::uuid()->toString());
            }
        });
    }

    public static function getPositionAccessGroupList($groupId){

       $query =  self::select('hr_position.ID','hr_position.MASTER_NAME')
                    ->leftJoin('master_position_access_group', 'master_position_access_group.position_id', '=', 'hr_position.ID')
                    ->where('master_position_access_group.group_id', $groupId)
                    ->get(); 

        return $query;
    }
}
