<?php

namespace App\Core\Application\UseCases\Payments\Staff\Concepts;

use App\Core\Application\Mappers\EnumMapper;
use App\Core\Domain\Entities\PaymentConcept;
use App\Core\Domain\Enum\PaymentConcept\PaymentConceptStatus;
use App\Core\Domain\Repositories\Command\Payments\PaymentConceptRepInterface;
use App\Core\Domain\Repositories\Query\User\UserQueryRepInterface;
use App\Core\Domain\Utils\Validators\PaymentConceptValidator;
use App\Jobs\ClearCacheWhileStatusChangeJob;

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
        PaymentConceptValidator::ensureValidStatusTransition($concept, EnumMapper::toPaymentConceptStatus('finalizado'));
        $users=$this->uqRepo->getRecipients($concept,$concept->applies_to->value);
        foreach ($users as $user)
        {
            ClearCacheWhileStatusChangeJob::dispatch($user->id, PaymentConceptStatus::FINALIZADO)->delay(now()->addSeconds(rand(1, 10)));
        }
        return $this->pcRepo->finalize($concept);
    }
}
