<?php

namespace App\Core\Application\UseCases\Career;

use App\Core\Domain\Entities\Career;
use App\Core\Domain\Repositories\Command\CareerRepInterface;

class CreateCareerUseCase
{
    public function __construct(private CareerRepInterface $careerRepo)
    {
    }

    public function execute(Career $career): Career
    {
        return $this->careerRepo->create($career);
    }
}
