<?php

namespace App\Core\Application\UseCases\Jobs;

use App\Core\Application\Traits\HasPaymentConcept;
use App\Core\Domain\Entities\PaymentConcept;
use App\Core\Domain\Repositories\Query\User\UserQueryRepInterface;
use Illuminate\Support\Facades\Log;

class ProcessPaymentConceptRecipientsUseCase
{
    use HasPaymentConcept;

    public function __construct(
        private UserQueryRepInterface $uqRepo,
    )
    {
        $this->setRepository($uqRepo);
    }

    public function execute(PaymentConcept $paymentConcept, string $appliesTo): void
    {
        $recipients = $this->uqRepo->getRecipients($paymentConcept, $appliesTo);
        if(empty($recipients)){
            Log::warning('Payment concept created but no recipients found for notifications', [
                'concept_id' => $paymentConcept->id,
                'applies_to' => $appliesTo
            ]);
            return;
        }
        $this->notifyRecipients($paymentConcept,$recipients);
    }


}
