<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = [
        'id_user',
        'id_concept',
        'id_payment_method',
        'status',
        'transaction_date',
        'payment_intent_id',
        'url'
    ];

    public function users(){
        return $this->belongsTo(User::class,'id_user');
    }
    public function paymentConcepts(){
        return $this->belongsTo(PaymentConcept::class,'id_concept');
    }
    public function paymentMethods(){
        return $this->belongsTo(PaymentMethod::class,'id_payment_method');
    }
}
