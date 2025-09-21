<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Roles;
use App\Models\User;


class UserRole extends Model
{
    protected $fillable = ['id_user','id_role'];


    public function user(){
        return $this->belongsTo(User::class,'id_user');
    }

    public function role(){
        return $this->belongsTo(Roles::class,'id_role');
    }


}
