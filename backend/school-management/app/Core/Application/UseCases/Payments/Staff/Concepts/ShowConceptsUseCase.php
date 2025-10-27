<?php

namespace App\Core\Application\UseCases\Payments\Staff\Concepts;

use App\Core\Domain\Repositories\Query\Payments\PaymentConceptQueryRepInterface;
use App\Core\Domain\Utils\Validators\PaymentConceptValidator;

class ShowConceptsUseCase
{
        public function __construct(
        private PaymentConceptQueryRepInterface $pcqRepo,

    )
    {}
    public function execute(string $status): array {
        PaymentConceptValidator::ensureValidStatus($status);
        return $this->pcqRepo->findAllConcepts($status);
    }
}
