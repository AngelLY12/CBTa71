<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\PaymentConcept;

class PaymentConceptSemester extends Model
{

    use HasFactory;
    protected $fillable = [
        'payment_concept_id',
        'semestre'
    ];
    protected $table = 'payment_concept_semester';
    public function paymentConcept(){
        return $this->belongsTo(PaymentConcept::class);
    }
}
