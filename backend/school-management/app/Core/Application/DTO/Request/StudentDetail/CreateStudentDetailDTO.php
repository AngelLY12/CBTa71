<?php

namespace App\Core\Application\DTO\Request\StudentDetail;

class CreateStudentDetailDTO{
        public function __construct(
        public int $user_id,
        public ?int $career_id = null,
        public ?int $n_control = null,
        public ?int $semestre = null,
        public ?string $group = null,
        public ?string $workshop = null,
    ) {}
}
