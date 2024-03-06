<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class M_MasterMenu extends Model
{
    use HasFactory;
    protected $table = 'master_menu';
    protected $fillable = [
        'id',
        'menu_name',
        'route',
        'parent',
        'order',
        'leading',
        'action',
        'status',
        'ability',
        'created_by',
        'updated_by',
        'deleted_by',
        'deleted_at'
    ];
    protected $guarded = [];
    public $incrementing = false;
    protected $keyType = 'string';
    protected $primaryKey = 'id';
    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if ($model->getKey() == null) {
                $model->setAttribute($model->getKeyName(), Str::uuid()->toString());
            }
        });
    }
}
