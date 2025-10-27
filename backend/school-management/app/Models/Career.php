<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\PaymentConcept;

class Career extends Model
{
    use HasFactory;
    protected $fillable = ['career_name'];

    //Relaciones
    public function users(){
        return $this->hasMany(User::class);
    }

    public function paymentConcepts(){
        return $this->belongsToMany(PaymentConcept::class);
    }
    public function studentDetails(){
        return $this->hasMany(StudentDetail::class);
    }

    public function students()
    {
        return $this->hasManyThrough(User::class, StudentDetail::class, 'career_id', 'id', 'id', 'user_id');
    }

}
