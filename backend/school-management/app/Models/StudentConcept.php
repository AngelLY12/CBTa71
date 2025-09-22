<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\PaymentConcept;
use App\Models\User;

class StudentConcept extends Model
{
    protected $fillable =[
        'id_concept',
        'id_user'
    ];

    public function user(){
        return $this->belongsTo(User::class,'id_user');
    }
    public function concept(){
        return $this->belongsTo(PaymentConcept::class,'id_concept');
    }
}
