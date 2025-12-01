<?php

namespace App\Models;

use App\Core\Domain\Enum\PaymentConcept\PaymentConceptAppliesTo;
use App\Core\Domain\Enum\PaymentConcept\PaymentConceptStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Career;
use App\Models\User;
use App\Models\Payment;
use App\Models\PaymentConceptSemester;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class PaymentConcept extends Model
{
    use HasFactory, LogsActivity;
    protected $fillable = [
        'concept_name',
        'description',
        'status',
        'start_date',
        'end_date',
        'amount',
        'applies_to',
        'is_global'
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' =>  'date',
            'amount' => 'decimal:2',
            'status' => PaymentConceptStatus::class,
            'applies_to' => PaymentConceptAppliesTo::class
        ];
    }

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

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['concept_name', 'description' ,'status', 'amount'])
            ->logOnlyDirty()
            ->useLogName('paymentConcept');
    }

}
