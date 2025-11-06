<?php

namespace App\Core\Domain\Entities;

/**
 * @OA\Schema(
 *     schema="DomainStudentDetail",
 *     type="object",
 *     description="Detalles del estudiante",
 *     @OA\Property(property="id", type="integer", nullable=true, example=1),
 *     @OA\Property(property="user_id", type="integer", example=123),
 *     @OA\Property(property="career_id", type="integer", nullable=true, example=10),
 *     @OA\Property(property="n_control", type="integer", nullable=true, example=20201234),
 *     @OA\Property(property="semestre", type="integer", nullable=true, example=5),
 *     @OA\Property(property="group", type="string", nullable=true, example="A"),
 *     @OA\Property(property="workshop", type="string", nullable=true, example="Taller de programación")
 * )
 */
class StudentDetail{
    public function __construct(
        public ?int $id=null,
        public int $user_id,
        public ?int $career_id = null,
        public ?int $n_control = null,
        public ?int $semestre = null,
        public ?string $group = null,
        public ?string $workshop = null,
    ) {}

    public function promote(): void {
        if ($this->semestre !== null) {
            $this->semestre++;
        }
    }

    public function assignGroup(string $group): void {
        $this->group = $group;
    }
}
