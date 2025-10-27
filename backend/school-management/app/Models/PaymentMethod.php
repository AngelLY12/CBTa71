<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentMethod extends Model
{
    protected $fillable =[
        'user_id',
        'stripe_payment_method_id',
        'brand',
        'last4',
        'exp_month',
        'exp_year'
    ];

    protected $hidden = ['user_id,created_at', 'updated_at'];


     public function user(){
        return $this->belongsTo(User::class);
    }

    public function payments(){
        return $this->hasMany(PaymentMethod::class);
    }


}
