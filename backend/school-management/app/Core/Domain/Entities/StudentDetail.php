<?php

namespace App\Core\Domain\Entities;

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
