<?php

namespace App\Core\Domain\Repositories\Command\Stripe;

use App\Core\Domain\Entities\PaymentConcept;
use App\Core\Domain\Entities\User;
use Stripe\Checkout\Session;
use Stripe\PaymentIntent;

interface StripeGatewayInterface{
    public function createStripeUser(User $user):string;
    public function createSetupSession(User $user):Session;
    public function createCheckoutSession(User $user, PaymentConcept $paymentConcept, string $amount):Session;
    public function deletePaymentMethod(string $paymentMethodId):bool;
    public function expireSessionIfPending(string $sessionId): bool;



}
