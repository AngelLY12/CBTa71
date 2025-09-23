<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Career;
use App\Models\User;
use App\Models\Payment;
use App\Models\PaymentConceptSemester;


class PaymentConcept extends Model
{
    use HasFactory;
    protected $fillable = [
        'concept_name',
        'description',
        'status',
        'start_date',
        'end_date',
        'amount',
        'is_global'
    ];

    public function careerPaymentConcepts(){
        return $this->belongsToMany(Career::class);
    }

    public function users(){
        return $this->belongsToMany(User::class);
    }

    public function payments(){
        return $this->hasMany(Payment::class);
    }

    public function semesterPaymentConcepts(){
        return $this->hasMany(PaymentConceptSemester::class);
    }

}
