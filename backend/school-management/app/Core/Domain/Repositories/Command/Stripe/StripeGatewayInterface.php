<?php

namespace App\Core\Domain\Repositories\Command\Stripe;

use App\Core\Domain\Entities\PaymentConcept;
use App\Core\Domain\Entities\User;
use Stripe\Checkout\Session;
use Stripe\PaymentIntent;

interface StripeGatewayInterface{
    public function createStripeUser(User $user):string;
    public function createSetupSession(User $user):Session;
    public function getSetupIntentFromSession(string $sessionId);
    public function createCheckoutSession(User $user, PaymentConcept $paymentConcept):Session;
    public function retrievePaymentMethod(string $paymentMethodId);
    public function deletePaymentMethod(string $paymentMethodId):bool;
    public function getIntentAndCharge(string $paymentIntentId): array;
    public function getStudentPaymentsFromStripe(User $user, ?int $year): array;
    public function getPaymentIntentFromSession(string $sessionId): PaymentIntent;


}
