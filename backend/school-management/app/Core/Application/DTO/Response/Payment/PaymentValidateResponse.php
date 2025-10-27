<?php

namespace App\Core\Application\DTO\Response\Payment;

use App\Core\Application\DTO\Response\User\UserDataResponse;

class PaymentValidateResponse{

     public function __construct(
        public ?UserDataResponse $student,
        public ?PaymentDataResponse $payment,
    ) {}

}
