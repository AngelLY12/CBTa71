<?php

namespace App\Core\Application\UseCases\Parents;

use App\Core\Application\Mappers\ParentStudentMapper;
use App\Core\Domain\Repositories\Command\ParentInviteRepInterface;
use App\Core\Domain\Repositories\Command\ParentStudentRepInterface;
use App\Core\Domain\Repositories\Command\UserRepInterface;
use App\Core\Domain\Repositories\Query\ParentInviteQueryRepInterface;
use App\Core\Domain\Repositories\Query\ParentStudentQueryRepInterface;
use App\Core\Domain\Repositories\Query\UserQueryRepInterface;
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
        $hasParentRole= $this->userQRepo->hasRole($parent->id,'parent');
        if (!$hasParentRole) {
            $this->userRepo->assignRole($parent->id,'parent');
        }
        $parentRoles= $this->userQRepo->findUserRoles($parent->id);
        $studentRoles= $this->userQRepo->findUserRoles($student->id);
        $parentRole = collect($parentRoles)->firstWhere('name', 'parent');
        $studentRole = collect($studentRoles)->firstWhere('name', 'student');
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
