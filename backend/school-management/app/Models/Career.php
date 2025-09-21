<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\CareerConcept;

class Career extends Model
{
    protected $fillable = ['career_name'];


    public function users(){
        return $this->hasMany(User::class,'id_career');
    }

    public function concept(){
        return $this->hasMany(CareerConcept::class,'id_career');
    }

    public function paymentConcepts() {
        return $this->belongsToMany(PaymentConcept::class, 'career_concepts', 'id_career', 'id_concept')
                    ->withTimestamps();
    }

}
