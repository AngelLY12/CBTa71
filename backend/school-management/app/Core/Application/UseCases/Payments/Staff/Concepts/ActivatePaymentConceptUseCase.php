<?php
namespace App\Core\Application\UseCases\Payments\Staff\Concepts;

use App\Core\Application\Mappers\EnumMapper;
use App\Core\Domain\Entities\PaymentConcept;
use App\Core\Domain\Enum\PaymentConcept\PaymentConceptStatus;
use App\Core\Domain\Repositories\Command\Payments\PaymentConceptRepInterface;
use App\Core\Domain\Repositories\Query\UserQueryRepInterface;
use App\Core\Domain\Utils\Validators\PaymentConceptValidator;
use App\Jobs\ClearCacheWhileStatusChangeJob;
use App\Jobs\ClearStudentConceptCacheJob;
use App\Jobs\ClearStudentConceptOverdueCacheJob;

class ActivatePaymentConceptUseCase
{
    public function __construct(
        private PaymentConceptRepInterface $pcRepo,
        private UserQueryRepInterface $uqRepo
    )
    {}

    public function execute(PaymentConcept $concept):PaymentConcept
    {
        PaymentConceptValidator::ensureValidStatusTransition($concept, EnumMapper::toPaymentConceptStatus('activo'));
        $users=$this->uqRepo->getRecipients($concept,$concept->applies_to->value);
        foreach ($users as $user)
        {
           ClearCacheWhileStatusChangeJob::dispatch($user->id, PaymentConceptStatus::ACTIVO)->delay(now()->addSeconds(rand(1, 10)));
        }
        return $this->pcRepo->activate($concept);
    }
}
