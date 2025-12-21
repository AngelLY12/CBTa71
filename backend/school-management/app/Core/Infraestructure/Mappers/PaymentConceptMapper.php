<?php

namespace App\Core\Infraestructure\Mappers;

use App\Models\PaymentConcept;
use App\Core\Domain\Entities\PaymentConcept as DomainPaymentConcept;


class PaymentConceptMapper{

    public static function toDomain(PaymentConcept $paymentConcept){
        $domain= new DomainPaymentConcept(
            id:$paymentConcept->id,
            concept_name:$paymentConcept->concept_name,
            description:$paymentConcept->description,
            status:$paymentConcept->status,
            start_date:$paymentConcept->start_date,
            end_date:$paymentConcept->end_date,
            amount:$paymentConcept->amount,
            applies_to:$paymentConcept->applies_to,
            is_global:$paymentConcept->is_global
        );
        $domain->setCareerIds($paymentConcept->careers->pluck('id')->toArray());
        $domain->setUserIds($paymentConcept->users->pluck('id')->toArray());
        $domain->setSemesters($paymentConcept->paymentConceptSemesters->pluck('semestre')->toArray());
        $domain->setExceptionUsersIds($paymentConcept->exceptions->pluck('user_id')->toArray());
        $domain->setApplicantTag($paymentConcept->applicantTypes->pluck('tag')->toArray());

        return $domain;
    }

    public static function toPersistence(DomainPaymentConcept $concept): array
    {
        return [
            'concept_name' => $concept->concept_name,
            'description'  => $concept->description,
            'status'       => $concept->status,
            'start_date'   => $concept->start_date,
            'end_date'     => $concept->end_date,
            'amount'       => $concept->amount,
            'applies_to'   => $concept->applies_to,
            'is_global'    => $concept->is_global,
        ];
    }

}
