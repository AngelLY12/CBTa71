<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\PaymentConcept;

class Payment extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'payment_concept_id',
        'payment_method_id',
        'stripe_payment_method_id',
        'last4',
        'brand',
        'voucher_number',
        'spei_reference',
        'instructions_url',
        'type_payment_method',
        'status',
        'payment_intent_id',
        'url',
        'stripe_session_id'
    ];

    public function user(){
        return $this->belongsTo(User::class);
    }
    public function paymentConcept(){
        return $this->belongsTo(PaymentConcept::class);
    }
    public function paymentMethod(){
        return $this->belongsTo(PaymentMethod::class);
    }


}
