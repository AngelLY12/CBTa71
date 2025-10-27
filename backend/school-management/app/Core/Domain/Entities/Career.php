<?php

namespace App\Core\Domain\Entities;

class Career
{
    public function __construct(
        public string $career_name,
        public ?int $id=null,
    ) {}
}
