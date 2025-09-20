<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Roles;
use App\Models\Permissions;


class Roles_permissions extends Model
{
    protected $fillable = ['id_role','id_permission'];

    public function role(){
        return $this->belongsTo(Roles::class, 'id_role');
    }

    public function permission(){
        return $this->belongsTo(Permissions::class, 'id_permission');
    }





}
