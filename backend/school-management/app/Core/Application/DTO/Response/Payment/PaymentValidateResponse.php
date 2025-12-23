<?php

namespace App\Core\Application\DTO\Response\Payment;

use App\Core\Application\DTO\Response\User\UserDataResponse;

/**
 * @OA\Schema(
 *     schema="PaymentValidateResponse",
 *     type="object",
 *     @OA\Property(property="student", ref="#/components/schemas/UserDataResponse", nullable=true),
 *     @OA\Property(property="payment", ref="#/components/schemas/PaymentDataResponse", nullable=true),
 *     @OA\Property(property="updatedAt", type="string", format="date-time", example="2024-01-12 14:45:00"),
 * )
 */
class PaymentValidateResponse{

     public function __construct(
        public ?UserDataResponse $student,
        public ?PaymentDataResponse $payment,
        public readonly string $updatedAt
    ) {}

}
