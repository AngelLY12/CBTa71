<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Roles_permissions;


class Permissions extends Model
{
    protected $fillable = ['permission_name'];

    public function roles_permission(){
        return $this->hasMany(Roles_permissions::class,'id_permission');
    }

}
