<?php

namespace App\Core\Domain\Entities;

use Carbon\Carbon;

/**
 * @OA\Schema(
 *     schema="DomainUser",
 *     type="object",
 *     description="Representa un usuario del sistema",
 *     @OA\Property(property="id", type="integer", nullable=true, example=1),
 *     @OA\Property(property="name", type="string", example="Juan"),
 *     @OA\Property(property="last_name", type="string", example="Pérez"),
 *     @OA\Property(property="email", type="string", format="email", example="juan.perez@example.com"),
 *     @OA\Property(property="password", type="string", example="hashed_password"),
 *     @OA\Property(property="phone_number", type="string", example="+5215512345678"),
 *     @OA\Property(property="birthdate", type="string", format="date", nullable=true, example="1995-06-15"),
 *     @OA\Property(property="gender", type="string", nullable=true, example="male"),
 *     @OA\Property(property="curp", type="string", example="PEMJ950615HDFRZN09"),
 *     @OA\Property(property="address", type="array", nullable=true, @OA\Items(type="string"), example={"Calle Falsa 123", "Colonia Centro"}),
 *     @OA\Property(property="stripe_customer_id", type="string", nullable=true, example="cus_ABC123XYZ"),
 *     @OA\Property(property="blood_type", type="string", nullable=true, example="O+"),
 *     @OA\Property(property="registration_date", type="string", format="date-time", nullable=true, example="2024-01-15T12:34:56Z"),
 *     @OA\Property(property="status", type="string", nullable=true, example="activo"),
 *     @OA\Property(property="studentDetail", ref="#/components/schemas/DomainStudentDetail", nullable=true)
 * )
 */
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
    public function isDeleted():bool
    {
        return $this->status==='eliminado';
    }

    public function isDisable():bool
    {
        return $this->status==='baja';
    }
    public function setStudentDetail(StudentDetail $detail): void
    {
        $this->studentDetail = $detail;
    }
    public function getStudentDetail(){return $this->studentDetail;}

}
