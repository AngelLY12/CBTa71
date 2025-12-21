<?php

namespace App\Core\Application\Services\Payments\Staff;

use App\Core\Application\Traits\HasPaymentStripe;
use App\Core\Domain\Entities\Payment;
use App\Core\Domain\Entities\User;
use App\Core\Domain\Enum\Payment\PaymentStatus;
use App\Core\Domain\Repositories\Command\Payments\PaymentRepInterface;
use App\Core\Domain\Repositories\Query\Payments\PaymentConceptQueryRepInterface;
use App\Core\Domain\Repositories\Query\Payments\PaymentMethodQueryRepInterface;
use App\Core\Domain\Repositories\Query\Payments\PaymentQueryRepInterface;
use App\Core\Domain\Repositories\Query\Stripe\StripeGatewayQueryInterface;
use App\Core\Domain\Repositories\Query\User\UserQueryRepInterface;
use App\Exceptions\NotFound\ConceptNotFoundException;
use App\Exceptions\NotFound\PaymentMethodNotFoundException;
use App\Exceptions\NotFound\UserNotFoundException;

class PaymentValidationService
{
    use HasPaymentStripe;
    public function __construct(
        private UserQueryRepInterface $uqRepo,
        private PaymentRepInterface $paymentRepo,
        private PaymentQueryRepInterface $pqRepo,
        private StripeGatewayQueryInterface $stripeRepo,
        private PaymentMethodQueryRepInterface $pmqRepo,
        private PaymentConceptQueryRepInterface $pcqRepo
    ) {
        $this->setRepository($this->paymentRepo);

    }

    public function validateAndGetOrCreatePayment(
        string $search,
        string $payment_intent_id
    ): array {
        $student = $this->uqRepo->findBySearch($search);

        if (!$student) {
            throw new UserNotFoundException();
        }

        $payment = $this->pqRepo->findByIntentOrSession($student->id, $payment_intent_id);
        $wasValidated = false;

        if (!$payment) {
            [$payment, $wasValidated] = $this->createNewPayment($student, $payment_intent_id);
        } else {
            $wasValidated = $this->reconcileExistingPayment($payment, $payment_intent_id);
        }

        return [$payment, $student, $wasValidated];
    }

    private function createNewPayment(User $student, string $payment_intent_id): array
    {
        $stripe = $this->stripeRepo->getIntentAndCharge($payment_intent_id);

        $paymentConceptId = $stripe['intent']->metadata->payment_concept_id ?? null;
        $paymentConcept = $this->pcqRepo->findById($paymentConceptId);

        if (!$paymentConcept) {
            throw new ConceptNotFoundException();
        }

        $paymentMethod = $this->pmqRepo->findByStripeId($stripe['charge']->payment_method);

        if (!$paymentMethod) {
            throw new PaymentMethodNotFoundException();
        }

        $paymentMethodDetails = $this->formatPaymentMethodDetails(
            $stripe['charge']->payment_method_details
        );

        $payment = new Payment(
            user_id: $student->id,
            payment_concept_id: $paymentConceptId,
            payment_method_id: $paymentMethod->id,
            stripe_payment_method_id: $stripe['charge']->payment_method,
            concept_name: $paymentConcept->concept_name,
            amount: $paymentConcept->amount,
            amount_received: $stripe['charge']->amount_received,
            payment_method_details: $paymentMethodDetails,
            status: $stripe['intent']->status,
            payment_intent_id: $payment_intent_id,
            url: $stripe['charge']->receipt_url,
            stripe_session_id: $stripe['intent']->latest_charge->id,
            created_at: now()
        );

        $payment = $this->paymentRepo->create($payment);

        return [$payment, true];
    }

    private function reconcileExistingPayment(Payment $payment, string $payment_intent_id): bool
    {
        if ($payment->status === PaymentStatus::PAID && empty($payment->payment_method_details)) {
            logger()->info("Reconciling existing payment ID={$payment->id}");

            $stripe = $this->stripeRepo->getIntentAndCharge($payment_intent_id);
            $paymentMethod = $this->pmqRepo->findByStripeId($stripe['charge']->payment_method);

            if (!$paymentMethod) {
                throw new PaymentMethodNotFoundException();
            }

            $this->updatePaymentWithStripeData(
                $payment,
                $stripe['intent'],
                $stripe['charge'],
                $paymentMethod
            );

            return true;
        }

        return false;
    }

}
