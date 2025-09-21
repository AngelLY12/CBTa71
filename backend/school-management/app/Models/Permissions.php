<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\RolesPermissions;


class Permissions extends Model
{
    protected $fillable = ['permission_name'];

    public function roles_permission(){
        return $this->hasMany(RolesPermissions::class,'id_permission');
    }

}
