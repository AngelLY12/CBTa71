<?php

namespace App\Models;

use App\Core\Domain\Enum\PaymentConcept\PaymentConceptApplicantType;
use Illuminate\Database\Eloquent\Model;

class PaymentConceptApplicantTag extends Model
{
    protected $fillable = [
        'payment_concept_id',
        'tag'
    ];
    protected $table = 'payment_concept_semester';
    public function paymentConcept(){
        return $this->belongsTo(PaymentConcept::class);
    }

    protected function casts(): array
    {
        return [
            'tag' => PaymentConceptApplicantType::class
        ];
    }
}
