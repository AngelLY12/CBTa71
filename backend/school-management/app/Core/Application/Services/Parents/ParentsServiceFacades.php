<?php

namespace App\Core\Application\Services\Parents;

use App\Core\Application\DTO\Response\Parents\ParentChildrenResponse;
use App\Core\Application\Traits\HasCache;
use App\Core\Application\UseCases\Parents\AcceptParentInvitationUseCase;
use App\Core\Application\UseCases\Parents\GetParentChildrenUseCase;
use App\Core\Application\UseCases\Parents\SendParentInviteUseCase;
use App\Core\Domain\Entities\ParentInvite;
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
        private CacheService $service
    )
    {
        $this->setCacheService($service);
    }

    public function sendInvitation(int $studentId, string $parentEmail, int $createdBy): ParentInvite
    {
        return $this->send->execute($studentId,$parentEmail,$createdBy);
    }

    public function acceptInvitation(string $token, ?string $relationship=null, int $userId): void
    {
        $this->accept->execute($token, $relationship);
        $this->service->clearKey(CachePrefix::ADMIN->value, AdminCacheSufix::USERS->value . ":all");
        $this->service->clearKey(CachePrefix::PARENT->value, ParentCacheSufix::CHILDREN->value . ":$userId");
    }

    public function getParentChildren(int $parentId): ParentChildrenResponse
    {
        $key = $this->service->makeKey(CachePrefix::PARENT->value, ParentCacheSufix::CHILDREN->value . ":$parentId");
        return $this->cache($key, false, fn() => $this->children->execute($parentId));
    }
}
