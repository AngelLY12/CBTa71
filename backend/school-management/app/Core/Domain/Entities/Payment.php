<?php

namespace App\Core\Domain\Entities;

use App\Core\Domain\Enum\Payment\PaymentStatus;
use Carbon\Carbon;

/**
 * @OA\Schema(
 *     schema="DomainPayment",
 *     type="object",
 *     description="Representa un pago realizado por un usuario",
 *     @OA\Property(property="id", type="integer", nullable=true, example=1),
 *     @OA\Property(property="user_id", type="integer", example=123),
 *     @OA\Property(property="payment_concept_id", type="integer", nullable=true, example=45),
 *     @OA\Property(property="payment_method_id", type="integer", nullable=true, example=2),
 *     @OA\Property(property="stripe_payment_method_id", type="string", nullable=true, example="pm_1Hh1Xx2eZvKYlo2Cj1234567"),
 *     @OA\Property(property="concept_name", type="string", nullable=true, example="Pago de inscripción"),
 *     @OA\Property(property="amount", type="string", nullable=true, example="1500"),
 *     @OA\Property(property="amount_received", type="string", nullable=true, example="1500"),
 *     @OA\Property(property="payment_method_details", type="array", nullable=true,
 *         @OA\Items(type="string"),
 *         example={"Tarjeta de crédito", "Banco XYZ"}
 *     ),
 *     @OA\Property(property="status", ref="#/components/schemas/PaymentStatus", example="pendiente"),
 *     @OA\Property(property="payment_intent_id", type="string", nullable=true, example="pi_1Hh1Xx2eZvKYlo2Cd1234567"),
 *     @OA\Property(property="url", type="string", nullable=true, example="https://checkout.stripe.com/pay/cs_test_a1b2c3d4"),
 *     @OA\Property(property="stripe_session_id", type="string", nullable=true, example="cs_test_a1b2c3d4"),
 *     @OA\Property(property="created_at", type="string", format="date", example="2025-09-01"),
 * )
 */
class Payment
{
    public function __construct(
        public ?int $id = null,
        /** @var User */
        public int $user_id,
        /** @var PaymentConcept */
        public ?int $payment_concept_id,
        /** @var PaymentMethod */
        public ?int $payment_method_id=null,
        public ?string $stripe_payment_method_id=null,
        public ?string $concept_name,
        public ?string $amount,
        public ?string $amount_received,
        public ?array $payment_method_details = [],
        /** @var PaymentStatus */
        public PaymentStatus $status,
        public ?string $payment_intent_id=null,
        public ?string $url,
        public ?string $stripe_session_id,
        public ?Carbon $created_at,
    ) {}
    private function amountAsFloat(?string $value): float
    {
        return $value !== null ? (float) $value : 0.0;
    }

    public function getPendingAmount(): string
    {
        if ($this->amount === null || $this->amount_received === null) {
            return '0.00';
        }
        $expected = $this->amountAsFloat($this->amount);
        $received = $this->amountAsFloat($this->amount_received);
        if ($received >= $expected) {
            return '0.00';
        }
        return number_format($expected - $received, 2, '.', '');

    }

    public function getOverPaidAmount(): string
    {
        if ($this->amount === null || $this->amount_received === null) {
            return '0.00';
        }
        $expected = $this->amountAsFloat($this->amount);
        $received = $this->amountAsFloat($this->amount_received);
        if ($received <= $expected) {
            return '0.00';
        }
        return number_format($received - $expected, 2, '.', '');
    }

    public function isOverPaid(): bool
    {
        return $this->status === PaymentStatus::OVERPAID;
    }

    public function isUnderPaid():bool
    {
        return $this->status === PaymentStatus::UNDERPAID;
    }


}
