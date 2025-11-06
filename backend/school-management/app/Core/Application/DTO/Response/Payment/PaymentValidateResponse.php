<?php

namespace App\Core\Application\DTO\Response\Payment;

use App\Core\Application\DTO\Response\User\UserDataResponse;

/**
 * @OA\Schema(
 *     schema="PaymentValidateResponse",
 *     type="object",
 *     @OA\Property(property="student", ref="#/components/schemas/UserDataResponse", nullable=true),
 *     @OA\Property(property="payment", ref="#/components/schemas/PaymentDataResponse", nullable=true)
 * )
 */
class PaymentValidateResponse{

     public function __construct(
        public ?UserDataResponse $student,
        public ?PaymentDataResponse $payment,
    ) {}

}
