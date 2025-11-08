<?php

namespace App\Core\Application\UseCases\Payments;

use App\Core\Domain\Entities\PaymentConcept;
use App\Core\Domain\Repositories\Query\Payments\PaymentConceptQueryRepInterface;
use App\Exceptions\NotFound\ConceptNotFoundException;

class FindConceptByIdUseCase
{
    public function __construct(
        private PaymentConceptQueryRepInterface $pcqRepo
    )
    {
    }

    public function execute(int $id): PaymentConcept
    {
        $concept=  $this->pcqRepo->findById($id);
        if(!$concept){
            throw new ConceptNotFoundException();
        }
        return $concept;
    }
}
