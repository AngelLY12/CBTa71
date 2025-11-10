<?php

namespace App\Core\Application\UseCases\Payments\Staff\Concepts;

use App\Core\Domain\Entities\PaymentConcept;
use App\Core\Domain\Repositories\Command\Payments\PaymentConceptRepInterface;
use App\Core\Domain\Repositories\Query\UserQueryRepInterface;
use App\Core\Domain\Utils\Validators\PaymentConceptValidator;
use App\Jobs\ClearStudentConceptCacheJob;
use App\Jobs\ClearStudentConceptOverdueCacheJob;

class FinalizePaymentConceptUseCase
{
    public function __construct(
        private PaymentConceptRepInterface $pcRepo,
        private UserQueryRepInterface $uqRepo
    )
    {}

    public function execute(PaymentConcept $concept):PaymentConcept
    {
        PaymentConceptValidator::ensureConceptHasStarted($concept);
        PaymentConceptValidator::ensureValidStatusTransition($concept, 'finalizado');
        $users=$this->uqRepo->getRecipients($concept,$concept->applies_to);
        foreach ($users as $user)
        {
            ClearStudentConceptCacheJob::dispatch($user->id)->delay(now()->addSeconds(rand(1, 10)));
            ClearStudentConceptOverdueCacheJob::dispatch($user->id)->delay(now()->addSeconds(rand(1, 10)));
        }
        return $this->pcRepo->finalize($concept);
    }
}
