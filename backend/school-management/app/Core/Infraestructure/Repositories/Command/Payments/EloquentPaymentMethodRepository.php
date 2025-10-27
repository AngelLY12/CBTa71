<?php

namespace App\Core\Infraestructure\Repositories\Command\Payments;

use App\Core\Domain\Entities\PaymentMethod;
use App\Core\Domain\Entities\User;
use App\Core\Domain\Repositories\Command\Payments\PaymentMethodRepInterface;
use App\Core\Infraestructure\Mappers\PaymentMethodMapper;
use App\Models\PaymentMethod as EloquentPaymentMethod;

class EloquentPaymentMethodRepository implements PaymentMethodRepInterface
{

    public function findById(int $id): ?PaymentMethod
    {
        return optional(EloquentPaymentMethod::find($id), fn($pc) => PaymentMethodMapper::toDomain($pc));
    }
    public function create(PaymentMethod $paymentMethod):PaymentMethod
    {
        $pm = EloquentPaymentMethod::create(PaymentMethodMapper::toPersistence($paymentMethod));
        return PaymentMethodMapper::toDomain($pm);
    }
    public function findByStripeId(string $stripeId): ?PaymentMethod
    {
        return optional(EloquentPaymentMethod::where('stripe_payment_method_id', $stripeId)->first(), fn($pm) => PaymentMethodMapper::toDomain($pm));
    }
    public function delete(PaymentMethod $paymentMethod):void
    {
        EloquentPaymentMethod::findOrFail($paymentMethod->id)->delete();

    }
    public function getByUserId(User $user): array
    {
        $methods = EloquentPaymentMethod::where('user_id', $user->id)->get();
        return $methods->map(fn($pm) => PaymentMethodMapper::toDomain($pm))->toArray();
    }
}
