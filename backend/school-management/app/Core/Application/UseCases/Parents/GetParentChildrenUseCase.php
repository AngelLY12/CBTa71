<?php

namespace App\Core\Application\UseCases\Parents;

use App\Core\Application\DTO\Response\Parents\ParentChildrenResponse;
use App\Core\Domain\Enum\User\UserRoles;
use App\Core\Domain\Repositories\Query\User\ParentStudentQueryRepInterface;
use App\Core\Domain\Repositories\Query\User\UserQueryRepInterface;
use App\Exceptions\NotAllowed\UserInvalidRoleException;
use App\Exceptions\NotFound\ParentChildrenNotFoundException;
use App\Exceptions\NotFound\UserNotFoundException;

class GetParentChildrenUseCase
{
    public function __construct(
        private ParentStudentQueryRepInterface $relationQRepo,
        private UserQueryRepInterface $userQRepo,
    ) {}

    public function execute(int $parentId): ParentChildrenResponse
    {
        $parent=$this->userQRepo->findById($parentId);
        if(!$parent)
        {
            throw new UserNotFoundException();
        }
        if (!$this->userQRepo->hasRole($parentId, UserRoles::PARENT->value)) {
            throw new UserInvalidRoleException();
        }

        $response=$this->relationQRepo->getStudentsOfParent($parentId);
        if(!$response)
        {
            throw new ParentChildrenNotFoundException();
        }

        return $response;
    }
}
