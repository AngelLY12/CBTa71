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

    public function careers(){
        return $this->belongsToMany(Career::class);
    }

    public function users(){
        return $this->belongsToMany(User::class);
    }

    public function payments(){
        return $this->hasMany(Payment::class);
    }

    public function paymentConceptSemesters(){
        return $this->hasMany(PaymentConceptSemester::class);
    }

    public function scopePendingPaymentConcept($query, User $user=null){
        $query->where('status', 'activo')
        ->whereDate('start_date', '<=', now())
              ->where(function ($q) {
                  $q->whereNull('end_date')
                    ->orWhereDate('end_date', '>=', now());
              });
        if ($user) {
        $query ->whereDoesntHave('payments',fn($q) => $q->where('user_id', $user->id))
              ->where(function ($q) use ($user) {
                  $q->where('is_global', true)
                    ->orWhereHas('users', fn($q) => $q->where('users.id', $user->id))
                    ->orWhereHas('careers', fn($q) => $q->where('careers.id', $user->career_id))
                    ->orWhereHas('paymentConceptSemesters', fn($q) => $q->where('semestre', $user->semestre));
              });
        }else{
            $query ->whereDoesntHave('payments');
            $query->where(function($q){
            $q->where('is_global', true)
              ->orWhereHas('users', fn($q) => $q->role('student')->where('status','activo'))
              ->orWhereHas('careers', fn($q) => $q->whereHas('users', fn($q2) => $q2->role('student')->where('status','activo')));
            });
        }
    }

}
