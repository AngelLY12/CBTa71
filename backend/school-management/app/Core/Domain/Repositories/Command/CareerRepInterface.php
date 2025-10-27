<?php

namespace App\Core\Domain\Repositories\Command;

use App\Core\Domain\Entities\Career;

interface CareerRepInterface{
    public function findByName(string $careerName): ?Career;
    public function findAll(): ?array;

}
