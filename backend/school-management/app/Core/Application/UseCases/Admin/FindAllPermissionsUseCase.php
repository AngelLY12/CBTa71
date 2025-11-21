<?php

namespace App\Core\Application\UseCases\Admin;

use App\Core\Application\DTO\Response\General\PaginatedResponse;
use App\Core\Application\Mappers\GeneralMapper;
use App\Core\Domain\Repositories\Query\RolesAndPermissosQueryRepInterface;
use App\Exceptions\NotFound\RoleNotFoundException;

class FindAllPermissionsUseCase
{
    public function __construct(
        private RolesAndPermissosQueryRepInterface $rpqRepo
    )
    {
    }

    public function execute(string $roleName): array
    {
        $existsRole= $this->rpqRepo->findRoleByName($roleName);
        if(!$existsRole)
        {
            throw new RoleNotFoundException();
        }
        return $this->rpqRepo->findPermissionsByUserRole($roleName);
    }
}
