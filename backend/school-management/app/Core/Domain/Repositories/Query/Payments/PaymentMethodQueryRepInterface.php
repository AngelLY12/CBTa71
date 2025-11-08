<?php

namespace App\Core\Domain\Repositories\Query\Payments;

use App\Core\Domain\Entities\PaymentMethod;
use App\Core\Domain\Entities\User;

interface PaymentMethodQueryRepInterface
{
    public function findById(int $id):?PaymentMethod;
    public function findByStripeId(string $stripeId): ?PaymentMethod;
    public function getByUserId(int $userId): array;

}
