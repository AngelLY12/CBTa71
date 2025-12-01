<?php

namespace App\Core\Application\UseCases\Payments\Student\PendingPayment;

use App\Core\Application\Mappers\EnumMapper;
use App\Core\Domain\Entities\Payment;
use App\Core\Domain\Repositories\Command\Payments\PaymentRepInterface;
use App\Core\Domain\Repositories\Command\Stripe\StripeGatewayInterface;
use App\Core\Domain\Repositories\Query\Payments\PaymentConceptQueryRepInterface;
use App\Core\Domain\Repositories\Query\User\UserQueryRepInterface;
use App\Core\Domain\Utils\Validators\PaymentConceptValidator;
use App\Exceptions\NotFound\ConceptNotFoundException;
use Illuminate\Support\Facades\DB;

class PayConceptUseCase
{
    public function __construct(
        private PaymentConceptQueryRepInterface $pcqRepo,
        private PaymentRepInterface $paymentRepo,
        private StripeGatewayInterface $stripe,
        private UserQueryRepInterface $uqRepo
    ) {}
    public function execute(int $userId, int $conceptId): string {
        return DB::transaction(function() use ($userId, $conceptId) {

            $concept = $this->pcqRepo->findById($conceptId);
            if (!$concept) throw new ConceptNotFoundException();
            $user = $this->uqRepo->getUserWithStudentDetail($userId);
            PaymentConceptValidator::ensureConceptIsActiveAndValid($concept, $user);
            $session = $this->stripe->createCheckoutSession($user, $concept);
            $payment = new Payment(
                id:null,
                user_id: $user->id,
                payment_concept_id: $concept->id,
                payment_method_id: null,
                payment_intent_id: null,
                stripe_payment_method_id: null,
                concept_name:$concept->concept_name,
                amount:$concept->amount,
                payment_method_details: [],
                status: EnumMapper::fromStripe($session->payment_status),
                url: $session->url ?? null,
                stripe_session_id: $session->id ?? null
            );

            $this->paymentRepo->create($payment);
            return $session->url;
        });
    }

}
