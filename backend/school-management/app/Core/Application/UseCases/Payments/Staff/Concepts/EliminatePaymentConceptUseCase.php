<?php
namespace App\Core\Application\UseCases\Payments\Staff\Concepts;

use App\Core\Domain\Entities\PaymentConcept;
use App\Core\Domain\Repositories\Command\Payments\PaymentConceptRepInterface;
use App\Core\Domain\Repositories\Query\Payments\PaymentConceptQueryRepInterface;
use App\Core\Domain\Repositories\Query\UserQueryRepInterface;
use App\Jobs\ClearStudentConceptCacheJob;
use App\Jobs\ClearStudentConceptOverdueCacheJob;

class EliminatePaymentConceptUseCase
{
    public function __construct(
        private PaymentConceptRepInterface $pcRepo,
        private PaymentConceptQueryRepInterface $pcqRepo,
        private UserQueryRepInterface $uqRepo
    )
    {}

    public function execute(int $conceptId):void
    {

        $this->pcRepo->delete($conceptId);
        $concept=$this->pcqRepo->findById($conceptId);
        if($concept){
            $users=$this->uqRepo->getRecipients($concept,$concept->applies_to->value);
            foreach ($users as $user)
            {
                ClearStudentConceptCacheJob::dispatch($user->id)->delay(now()->addSeconds(rand(1, 10)));
                ClearStudentConceptOverdueCacheJob::dispatch($user->id)->delay(now()->addSeconds(rand(1, 10)));
            }
        }
    }
}
