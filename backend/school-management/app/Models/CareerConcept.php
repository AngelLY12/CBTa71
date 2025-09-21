<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Career;
use App\Models\PaymentConcept;

class CareerConcept extends Model
{
    protected $fillable = [
        'id_concept',
        'id_career'

    ];

    public function career(){
        return $this->belongsTo(Career::class,'id_career');
    }
    public function concept(){
        return $this->belongsTo(PaymentConcept::class,'id_concept');
    }
}
