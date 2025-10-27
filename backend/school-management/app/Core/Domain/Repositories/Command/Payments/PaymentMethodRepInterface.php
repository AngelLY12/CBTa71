<?php

namespace App\Core\Domain\Repositories\Command\Payments;

use App\Core\Domain\Entities\PaymentMethod;
use App\Core\Domain\Entities\User;

interface PaymentMethodRepInterface{
    public function create(PaymentMethod $paymentMethod):PaymentMethod;
    public function findById(int $id):?PaymentMethod;
    public function findByStripeId(string $stripeId): ?PaymentMethod;
    public function delete(PaymentMethod $paymentMethod):void;
    public function getByUserId(User $user): array;
}
