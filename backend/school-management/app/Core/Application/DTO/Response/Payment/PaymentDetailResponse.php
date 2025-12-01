<?php

namespace App\Core\Application\DTO\Response\Payment;


/**
 * @OA\Schema(
 *     schema="PaymentDetailResponse",
 *     type="object",
 *     @OA\Property(property="id", type="integer", nullable=true, description="ID interno del pago", example=123),
 *     @OA\Property(property="concept", type="string", nullable=true, description="Concepto del pago", example="Pago de inscripción"),
 *     @OA\Property(property="amount", type="string", nullable=true, description="Monto del pago", example="1500.00"),
 *     @OA\Property(property="date", type="string", nullable=true, description="Fecha del pago", example="2025-11-04"),
 *     @OA\Property(property="status", type="string", nullable=true, description="Estado del pago", example="completed"),
 *     @OA\Property(property="reference", type="string", nullable=true, description="Referencia del pago", example="REF123456"),
 *     @OA\Property(property="url", type="string", nullable=true, description="URL del recibo de pago", example="https://example.com/receipt/123"),
 *     @OA\Property(property="payment_method_details", type="array", nullable=true, description="Detalles del método de pago utilizado", @OA\Items(type="string"))
 * )
 */
class PaymentDetailResponse{
     public function __construct(
        public readonly ?int $id,
        public readonly ?string $concept,
        public readonly ?string $amount,
        public readonly ?string $date,
        public readonly ?string $status,
        public readonly ?string $reference,
        public readonly ?string $url,
        public readonly ?array $payment_method_details
    ) {}
}
