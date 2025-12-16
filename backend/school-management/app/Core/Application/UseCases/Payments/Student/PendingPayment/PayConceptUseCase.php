<?php

namespace App\Core\Application\UseCases\Payments\Student\PendingPayment;

use App\Core\Application\Mappers\EnumMapper;
use App\Core\Application\Mappers\PaymentMapper;
use App\Core\Domain\Entities\User;
use App\Core\Domain\Enum\Payment\PaymentStatus;
use App\Core\Domain\Repositories\Command\Payments\PaymentRepInterface;
use App\Core\Domain\Repositories\Command\Stripe\StripeGatewayInterface;
use App\Core\Domain\Repositories\Query\Payments\PaymentConceptQueryRepInterface;
use App\Core\Domain\Repositories\Query\Payments\PaymentQueryRepInterface;
use App\Core\Domain\Utils\Validators\PaymentConceptValidator;
use App\Exceptions\NotFound\ConceptNotFoundException;
use Illuminate\Support\Facades\DB;

class PayConceptUseCase
{
    public function __construct(
        private PaymentConceptQueryRepInterface $pcqRepo,
        private PaymentRepInterface $paymentRepo,
        private PaymentQueryRepInterface $paymentQueryRep,
        private StripeGatewayInterface $stripe,
    ) {}
    public function execute(User $user, int $conceptId): string {
        return DB::transaction(function() use ($user, $conceptId) {

            $concept = $this->pcqRepo->findById($conceptId);
            if (!$concept) throw new ConceptNotFoundException();
            PaymentConceptValidator::ensureConceptIsActiveAndValid($concept, $user);
            $lastPayment = $this->paymentQueryRep->getLastPaymentForConcept(
                $user->id,
                $conceptId,
                allowedStatuses: [PaymentStatus::UNDERPAID->value]
            );
            $amountToPay = $concept->amount;
            if ($lastPayment && $lastPayment->isUnderPaid()) {
                $amountToPay = $lastPayment->getPendingAmount();
            }
            $session = $this->stripe->createCheckoutSession($user, $concept, $amountToPay);


            if ($lastPayment) {
                $this->paymentRepo->update($lastPayment->id, [
                    'stripe_session_id' => $session->id,
                    'status' => EnumMapper::fromStripe($session->payment_status),
                ]);
            } else {
                $payment = PaymentMapper::toDomain(concept: $concept, userId: $user->id, session: $session);
                $this->paymentRepo->create($payment);
            }

            return $session->url;
        });
    }
}
