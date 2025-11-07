<?php

namespace App\Core\Application\UseCases\Admin;

use App\Core\Domain\Repositories\Query\RolesAndPermissosQueryRepInterface;

class FindAllRolesUseCase
{
    public function __construct(
        private RolesAndPermissosQueryRepInterface $rpqRepo
    )
    {
    }
    public function execute(): array
    {
        return $this->rpqRepo->findAllRoles();
    }
}
