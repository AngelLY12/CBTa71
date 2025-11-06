<?php

namespace App\Core\Application\UseCases\Payments;

use App\Core\Domain\Entities\PaymentConcept;
use App\Core\Domain\Repositories\Command\Payments\PaymentConceptRepInterface;
use App\Exceptions\NotFound\ConceptNotFoundException;

class FindConceptByIdUseCase
{
    public function __construct(
        private PaymentConceptRepInterface $pcRepo
    )
    {
    }

    public function execute(int $id): PaymentConcept
    {
        $concept=  $this->pcRepo->findById($id);
        if(!$concept){
            throw new ConceptNotFoundException();
        }
        return $concept;
    }
}
