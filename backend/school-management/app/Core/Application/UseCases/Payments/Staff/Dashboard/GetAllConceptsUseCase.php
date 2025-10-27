<?php

namespace App\Core\Application\UseCases\Payments\Staff\Dashboard;

use App\Core\Application\Mappers\PaymentConceptMapper;
use App\Core\Domain\Repositories\Query\Payments\PaymentConceptQueryRepInterface;

class GetAllConceptsUseCase
{
    public function __construct(
        private PaymentConceptQueryRepInterface $pcqRepo
    )
    {
    }
    public function execute(bool $onlyThisYear = false):array
    {
        $conceptsArray= $this->pcqRepo->getConceptsToDashboard($onlyThisYear);
        return $conceptsArray;
    }
}
