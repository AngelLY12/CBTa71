<?php

namespace App\Core\Application\DTO\Response\PaymentConcept;

class ConceptNameAndAmountResponse
{
    public function __construct(
        public readonly ?string $user_name,
        public readonly ?string $concept_name,
        public readonly ?int $amount,
    )
    {

    }
}
