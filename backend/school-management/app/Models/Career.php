<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Career extends Model
{
    protected $fillable = ['career_name'];


    public function users(){
        return $this->hasMany(User::class,'id_career');
    }

}
