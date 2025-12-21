<?php

namespace App\Core\Application\UseCases\Parents;

use App\Core\Application\Mappers\ParentStudentMapper;
use App\Core\Domain\Enum\User\UserRoles;
use App\Core\Domain\Repositories\Command\Misc\ParentInviteRepInterface;
use App\Core\Domain\Repositories\Command\User\ParentStudentRepInterface;
use App\Core\Domain\Repositories\Command\User\UserRepInterface;
use App\Core\Domain\Repositories\Query\Misc\ParentInviteQueryRepInterface;
use App\Core\Domain\Repositories\Query\User\UserQueryRepInterface;
use App\Exceptions\NotAllowed\InvalidInvitationException;
use App\Exceptions\NotFound\UserNotFoundException;

class AcceptParentInvitationUseCase
{
   public function __construct(
        private ParentInviteQueryRepInterface $inviteQRepo,
        private ParentInviteRepInterface $inviteRepo,
        private ParentStudentRepInterface $parentRepo,
        private UserQueryRepInterface $userQRepo,
        private UserRepInterface $userRepo,
    ) {}

    public function execute(string $token, ?string $relationship=null): void
    {
        $inv = $this->inviteQRepo->findByToken($token);

        if (!$inv || $inv->isUsed() || $inv->isExpired()) {
            throw new InvalidInvitationException();
        }

        $student=$this->userQRepo->findById($inv->studentId);
        $parent=$this->userQRepo->findUserByEmail($inv->email);
        if(!$student || !$parent)
        {
            throw new UserNotFoundException();
        }
        $hasParentRole= $parent->isParent();
        if (!$hasParentRole) {
            $this->userRepo->assignRole($parent->id,UserRoles::PARENT->value);
        }
        $parentRole = $parent->getRole(UserRoles::PARENT->value);
        $studentRole = $student->getRole(UserRoles::STUDENT->value);
        $data=[
            'parentId' => $parent->id,
            'studentId' => $student->id,
            'parentRoleId' => $parentRole->id,
            'studentRoleId' => $studentRole->id,
            'relationship' => $relationship ?? null
        ];
        $this->parentRepo->create(ParentStudentMapper::toDomain($data));
        $this->inviteRepo->markAsUsed($inv->id);

    }

}
