<?php
namespace App\Core\Domain\Repositories\Command\Payments;


use App\Core\Domain\Entities\Payment;


interface PaymentRepInterface {
    public function create(Payment $payment): Payment;
    public function findBySessionId(string $sessionId): ?Payment;
    public function findByIntentId(string $intentId): ?Payment;
    public function update(Payment $payment, array $fields): Payment;
    public function delete(Payment $payment):void;
}
