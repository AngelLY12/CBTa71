<?php

namespace App\Core\Application\DTO\Response\Payment;


/**
 * @OA\Schema(
 *     schema="PaymentListItemResponse",
 *     type="object",
 *     @OA\Property(property="date", type="string", nullable=true, description="Fecha del pago", example="2025-11-04"),
 *     @OA\Property(property="concept", type="string", nullable=true, description="Concepto del pago", example="Pago de inscripción"),
 *     @OA\Property(property="amount", type="string", nullable=true, description="Monto del pago", example="1500.00"),
 *     @OA\Property(property="method", type="string", nullable=true, description="Método de pago usado", example="Tarjeta de crédito"),
 *     @OA\Property(property="fullName", type="string", nullable=true, description="Nombre completo del usuario que realizó el pago", example="Juan Pérez")
 * )
 */
class PaymentListItemResponse{

     public function __construct(
        public readonly ?string $date,
        public readonly ?string $concept,
        public readonly ?string $amount,
        public readonly ?string $method,
        public readonly ?string $fullName

    ) {}
}
