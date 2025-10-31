<?php

namespace App\Core\Application\DTO\Request\User;

use Carbon\Carbon;

class CreateUserDTO
{
    public function __construct(
        public readonly string $name,
        public readonly string $last_name,
        public readonly string $email,
        public readonly string $password,
        public readonly string $phone_number,
        public readonly ?Carbon $birthdate,
        public readonly ?string $gender,
        public readonly string $curp,
        public readonly ?array $address,
        public readonly ?string $blood_type,
        public readonly ?Carbon $registration_date,
        public readonly ?string $status,
    )
    {

    }
}
