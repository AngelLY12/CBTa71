<?php

namespace App\Core\Application\UseCases\Payments\Student\PendingPayment;

use App\Core\Application\Mappers\PaymentMapper;
use App\Core\Domain\Entities\User;
use App\Core\Domain\Repositories\Command\Payments\PaymentRepInterface;
use App\Core\Domain\Repositories\Command\Stripe\StripeGatewayInterface;
use App\Core\Domain\Repositories\Query\Payments\PaymentConceptQueryRepInterface;
use App\Core\Domain\Utils\Validators\PaymentConceptValidator;
use App\Exceptions\NotFound\ConceptNotFoundException;
use Illuminate\Support\Facades\DB;

class PayConceptUseCase
{
    public function __construct(
        private PaymentConceptQueryRepInterface $pcqRepo,
        private PaymentRepInterface $paymentRepo,
        private StripeGatewayInterface $stripe,
    ) {}
    public function execute(User $user, int $conceptId): string {
        return DB::transaction(function() use ($user, $conceptId) {

            $concept = $this->pcqRepo->findById($conceptId);
            if (!$concept) throw new ConceptNotFoundException();
            PaymentConceptValidator::ensureConceptIsActiveAndValid($concept, $user);
            $session = $this->stripe->createCheckoutSession($user, $concept);
            $payment = PaymentMapper::toDomain(concept: $concept,userId: $user->id,session: $session);
            $this->paymentRepo->create($payment);
            return $session->url;
        });
    }

}
