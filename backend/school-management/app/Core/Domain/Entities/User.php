<?php

namespace App\Core\Domain\Entities;

use Carbon\Carbon;

class User
{
    public function __construct(
        public ?int $id=null,
        public string $name,
        public string $last_name,
        public string $email,
        public string $password,
        public string $phone_number,
        public ?Carbon $birthdate,
        public ?string $gender,
        public string $curp,
        public ?array $address,
        public ?string $stripe_customer_id,
        public ?string $blood_type,
        public ?Carbon $registration_date,
        public ?string $status,
        public ?StudentDetail $studentDetail=null
    ) {}

    public function fullName(): string
    {
        return "{$this->name} {$this->last_name}";
    }

    public function isActive(): bool
    {
        return $this->status === 'activo';
    }
    public function setStudentDetail(StudentDetail $detail): void
    {
        $this->studentDetail = $detail;
    }
    public function getStudentDetail(){return $this->studentDetail;}

}
