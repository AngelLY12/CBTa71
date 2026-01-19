<?php

namespace App\Core\Application\UseCases\Payments\Student\PendingPayment;

use App\Core\Application\Mappers\EnumMapper;
use App\Core\Application\Mappers\PaymentMapper;
use App\Core\Domain\Entities\User;
use App\Core\Domain\Enum\Payment\PaymentStatus;
use App\Core\Domain\Repositories\Command\Payments\PaymentRepInterface;
use App\Core\Domain\Repositories\Command\User\UserRepInterface;
use App\Core\Domain\Repositories\Query\Payments\PaymentConceptQueryRepInterface;
use App\Core\Domain\Repositories\Query\Payments\PaymentQueryRepInterface;
use App\Core\Domain\Repositories\Stripe\StripeGatewayInterface;
use App\Core\Domain\Utils\Validators\PaymentConceptValidator;
use App\Core\Domain\Utils\Validators\PaymentValidator;
use App\Exceptions\NotAllowed\PaymentRetryNotAllowedException;
use App\Exceptions\NotFound\ConceptNotFoundException;
use App\Exceptions\Validation\ValidationException;
use Illuminate\Support\Facades\DB;

class PayConceptUseCase
{
    public function __construct(
        private PaymentConceptQueryRepInterface $pcqRepo,
        private PaymentRepInterface $paymentRepo,
        private PaymentQueryRepInterface $paymentQueryRep,
        private UserRepInterface $userRep,
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
                allowedStatuses: PaymentStatus::nonTerminalStatuses()
            );

            $amountToPay = $concept->amount;
            if ($lastPayment && $lastPayment->isUnderPaid()) {
                $amountToPay = $lastPayment->getPendingAmount();
            }
            if($lastPayment && $lastPayment->isNonPaid())
            {
                PaymentValidator::ensurePaymentIsValidToRepay($lastPayment);
                if(!$this->stripe->expireSessionIfPending($lastPayment->stripe_session_id))
                {
                    throw new PaymentRetryNotAllowedException('El reintento de pago no es válido, espera a que expire la sesión anterior o realiza el pago con la sesión actual.');
                }
            }
            $customerId= $user->stripe_customer_id;
            if(!$customerId)
            {
                $createdCustomerId=$this->stripe->createStripeUser($user);
                $this->userRep->update($user->id, ['stripe_customer_id' => $createdCustomerId]);
                $customerId=$createdCustomerId;
            }
            $session = $this->stripe->createCheckoutSession($customerId, $concept, $amountToPay, $user->id);

            if ($lastPayment) {
                $this->paymentRepo->update($lastPayment->id, [
                    'stripe_session_id' => $session->id,
                ]);
            } else {
                $payment = PaymentMapper::toDomain(concept: $concept, userId: $user->id, session: $session);
                $this->paymentRepo->create($payment);
            }

            return $session->url;
        });
    }
}
