<?php

namespace App\Core\Domain\Entities;

use App\Core\Domain\Enum\User\UserBloodType;
use App\Core\Domain\Enum\User\UserGender;
use App\Core\Domain\Enum\User\UserStatus;
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
 *     @OA\Property(property="gender", ref="#/components/schemas/UserGender", nullable=true, example="male"),
 *     @OA\Property(property="curp", type="string", example="PEMJ950615HDFRZN09"),
 *     @OA\Property(property="address", type="array", nullable=true, @OA\Items(type="string"), example={"Calle Falsa 123", "Colonia Centro"}),
 *     @OA\Property(property="stripe_customer_id", type="string", nullable=true, example="cus_ABC123XYZ"),
 *     @OA\Property(property="blood_type", ref="#/components/schemas/UserBloodType", nullable=true, example="O+"),
 *     @OA\Property(property="registration_date", type="string", format="date-time", nullable=true, example="2024-01-15T12:34:56Z"),
 *     @OA\Property(property="status", ref="#/components/schemas/UserStatus", nullable=true, example="activo"),
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
        public ?UserGender $gender,
        public string $curp,
        public ?array $address,
        public ?string $stripe_customer_id,
        public ?UserBloodType $blood_type,
        public ?Carbon $registration_date,
        public ?UserStatus $status,
        public ?StudentDetail $studentDetail=null
    ) {}

    public function fullName(): string
    {
        return "{$this->name} {$this->last_name}";
    }

    public function isActive(): bool
    {
        return $this->status === UserStatus::ACTIVO;
    }
    public function isDeleted():bool
    {
        return $this->status=== UserStatus::ELIMINADO;
    }

    public function isDisable():bool
    {
        return $this->status=== UserStatus::BAJA;
    }
    public function setStudentDetail(StudentDetail $detail): void
    {
        $this->studentDetail = $detail;
    }
    public function getStudentDetail(){return $this->studentDetail;}
}
