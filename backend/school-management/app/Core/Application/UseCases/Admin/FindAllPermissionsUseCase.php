<?php

namespace App\Core\Application\UseCases\Admin;

use App\Core\Application\DTO\Response\General\PaginatedResponse;
use App\Core\Application\DTO\Response\General\PermissionsByUsers;
use App\Core\Application\Mappers\GeneralMapper;
use App\Core\Domain\Repositories\Query\RolesAndPermissosQueryRepInterface;
use App\Exceptions\NotFound\PermissionsByUserNotFoundException;
use App\Exceptions\NotFound\RoleNotFoundException;

class FindAllPermissionsUseCase
{
    public function __construct(
        private RolesAndPermissosQueryRepInterface $rpqRepo
    )
    {
    }

    public function execute(array $curps): PermissionsByUsers
    {
        $permissionsByUsers=$this->rpqRepo->findPermissionsApplicableByUsers($curps);
        if(empty($permissionsByUsers))
        {
            throw new PermissionsByUserNotFoundException();
        }
        return GeneralMapper::toPermissionsByUsers($permissionsByUsers);
    }
}
