<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class PaymentMethod extends Model
{
    protected $fillable = ['id_user','stripe_payment_method_id'];


    public function user(){
        return $this->belongsTo(User::class,'id_user');
    }


}
