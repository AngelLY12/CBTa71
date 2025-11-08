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

    public function execute(): array
    {
        return $this->rpqRepo->findAllPermissions();
    }
}
