<?php

namespace App\Core\Application\UseCases\Career;

use App\Core\Domain\Repositories\Command\CareerRepInterface;

class DeleteCareerUseCase
{
    public function __construct(private CareerRepInterface $careerRepo)
    {
    }

    public function execute(int $careerId)
    {
        return $this->careerRepo->delete($careerId);
    }
}
