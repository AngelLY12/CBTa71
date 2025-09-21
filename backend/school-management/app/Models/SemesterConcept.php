<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\PaymentConcept;

class SemesterConcept extends Model
{
    protected $fillable = [
        'id_concept',
        'semestre'
    ];
    public function concept(){
        return $this->belongsTo(PaymentConcept::class,'id_concept');
    }
}
