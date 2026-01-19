<?php

namespace App\Core\Application\Services\Parents;

use App\Core\Application\DTO\Response\Parents\ParentChildrenResponse;
use App\Core\Application\DTO\Response\Parents\StudentParentsResponse;
use App\Core\Application\Traits\HasCache;
use App\Core\Application\UseCases\Parents\AcceptParentInvitationUseCase;
use App\Core\Application\UseCases\Parents\DeleteParentStudentRelationUseCase;
use App\Core\Application\UseCases\Parents\GetParentChildrenUseCase;
use App\Core\Application\UseCases\Parents\GetStudentParentsUseCase;
use App\Core\Application\UseCases\Parents\SendParentInviteUseCase;
use App\Core\Domain\Entities\ParentInvite;
use App\Core\Domain\Entities\User;
use App\Core\Domain\Enum\Cache\AdminCacheSufix;
use App\Core\Domain\Enum\Cache\CachePrefix;
use App\Core\Domain\Enum\Cache\ParentCacheSufix;
use App\Core\Infraestructure\Cache\CacheService;

class ParentsServiceFacades
{
    use HasCache;
    public function __construct(
        private SendParentInviteUseCase $send,
        private AcceptParentInvitationUseCase $accept,
        private GetParentChildrenUseCase $children,
        private GetStudentParentsUseCase $parents,
        private DeleteParentStudentRelationUseCase $deleteRelation,
        private CacheService $service
    )
    {
        $this->setCacheService($service);
    }

    public function sendInvitation(int $studentId, string $parentEmail, int $createdBy): ParentInvite
    {
        return $this->send->execute($studentId,$parentEmail,$createdBy);
    }

    public function acceptInvitation(string $token, ?string $relationship=null): void
    {
        $this->accept->execute($token, $relationship);
    }

    public function getParentChildren(User $parent): ParentChildrenResponse
    {
        $key = $this->service->makeKey(CachePrefix::PARENT->value, ParentCacheSufix::CHILDREN->value . ":$parent->id");
        return $this->cache($key, false, fn() => $this->children->execute($parent));
    }

    public function getStudentParents(User $student): StudentParentsResponse
    {
        $key = $this->service->makeKey(CachePrefix::STUDENT->value, ParentCacheSufix::PARENTS->value . ":$student->id");
        return $this->cache($key, false, fn() => $this->parents->execute($student));
    }

    public function deleteParentStudentRelation(int $parentId, int $studentId): bool
    {
        $result=$this->deleteRelation->execute($parentId, $studentId);
        $this->service->clearKey(CachePrefix::STUDENT->value, ParentCacheSufix::PARENTS->value . ":$studentId");
        $this->service->clearKey(CachePrefix::PARENT->value, ParentCacheSufix::CHILDREN->value . ":$parentId");
        return $result;
    }

}
