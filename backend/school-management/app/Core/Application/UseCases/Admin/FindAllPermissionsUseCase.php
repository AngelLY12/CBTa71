<?php

namespace App\Core\Application\UseCases\Admin;

use App\Core\Application\DTO\Response\General\PaginatedResponse;
use App\Core\Application\Mappers\GeneralMapper;
use App\Core\Domain\Repositories\Query\RolesAndPermissosQueryRepInterface;

class FindAllPermissionsUseCase
{
    public function __construct(
        private RolesAndPermissosQueryRepInterface $rpqRepo
    )
    {
    }

    public function execute(int $page): PaginatedResponse
    {
        $permissions= $this->rpqRepo->findAllPermissions($page);
        return GeneralMapper::toPaginatedResponse($permissions->items(),$permissions);
    }
}
