<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Permissions;

class Roles extends Model
{
    protected $fillable = ['role_name'];

    public function users(){
        return $this->belongsToMany(User::class, 'users_roles', 'id_role', 'id_user');
    }

    public function permissions(){
        return $this->belongsToMany(Permissions::class, 'roles_permissions', 'id_role', 'id_permission');
    }

}
