<?php

namespace App\Core\Domain\Repositories\Command\Payments;

use App\Core\Domain\Entities\PaymentMethod;
use App\Core\Domain\Entities\User;

interface PaymentMethodRepInterface{
    public function create(PaymentMethod $paymentMethod):PaymentMethod;
    public function delete(int $paymentMethodId):void;
}
