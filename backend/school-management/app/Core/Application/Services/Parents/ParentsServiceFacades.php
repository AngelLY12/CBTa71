<?php

namespace App\Core\Application\Services\Parents;

use App\Core\Application\UseCases\Parents\AcceptParentInvitationUseCase;
use App\Core\Application\UseCases\Parents\SendParentInviteUseCase;
use App\Core\Domain\Entities\ParentInvite;
use App\Core\Infraestructure\Cache\CacheService;

class ParentsServiceFacades
{
    public function __construct(
        private SendParentInviteUseCase $send,
        private AcceptParentInvitationUseCase $accept,
        private CacheService $service
    )
    {
    }

    public function sendInvitation(int $studentId, string $parentEmail, int $createdBy): ParentInvite
    {
        return $this->send->execute($studentId,$parentEmail,$createdBy);
    }

    public function acceptInvitation(string $token, ?string $relationship=null): void
    {
        $this->accept->execute($token, $relationship);
        $this->service->clearPrefix("admin:users:all");
    }
}
