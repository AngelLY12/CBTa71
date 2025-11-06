<?php
namespace App\Core\Infraestructure\Repositories\Command\Payments;


use App\Core\Domain\Entities\Payment;
use App\Core\Domain\Repositories\Command\Payments\PaymentRepInterface;
use App\Core\Infraestructure\Mappers\PaymentMapper;
use App\Models\Payment as EloquentPayment;

class EloquentPaymentRepository implements PaymentRepInterface {


    public function create(Payment $payment): Payment
    {
        $pm = EloquentPayment::create(PaymentMapper::toPersistence($payment));
        return PaymentMapper::toDomain($pm);

    }

    public function findById(int $id): ?Payment
    {
        return optional(EloquentPayment::find($id), fn($pc) => PaymentMapper::toDomain($pc));
    }

    public function findBySessionId(string $sessionId): ?Payment
    {
        $payment= EloquentPayment::where('stripe_session_id', $sessionId)->first();
        return $payment ? PaymentMapper::toDomain($payment) : null;
    }

    public function findByIntentId(string $intentId): ?Payment
    {
        $payment=EloquentPayment::where('payment_intent_id', $intentId)->first();
        return $payment ? PaymentMapper::toDomain($payment) : null;
    }

    public function update(Payment $payment, array $fields): Payment
    {
        $eloquentPayment = EloquentPayment::findOrFail($payment->id);
        $eloquentPayment->update($fields);
        return PaymentMapper::toDomain($eloquentPayment);

    }

    public function delete(Payment $payment): void
    {
        EloquentPayment::findOrFail($payment->id)->delete();
    }

}

