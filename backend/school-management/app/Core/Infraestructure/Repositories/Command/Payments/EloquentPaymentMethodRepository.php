<?php

namespace App\Core\Infraestructure\Repositories\Command\Payments;

use App\Core\Domain\Entities\PaymentMethod;
use App\Core\Domain\Entities\User;
use App\Core\Domain\Repositories\Command\Payments\PaymentMethodRepInterface;
use App\Core\Infraestructure\Mappers\PaymentMethodMapper;
use App\Models\PaymentMethod as EloquentPaymentMethod;

class EloquentPaymentMethodRepository implements PaymentMethodRepInterface
{


    public function create(PaymentMethod $paymentMethod):PaymentMethod
    {
        $pm = EloquentPaymentMethod::create(PaymentMethodMapper::toPersistence($paymentMethod));
        return PaymentMethodMapper::toDomain($pm);
    }

    public function delete(int $paymentMethodId):void
    {
        EloquentPaymentMethod::findOrFail($paymentMethodId)->delete();

    }

}
