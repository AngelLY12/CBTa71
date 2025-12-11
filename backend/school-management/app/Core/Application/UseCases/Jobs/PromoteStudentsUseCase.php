<?php

namespace App\Core\Application\UseCases\Jobs;

use App\Core\Application\DTO\Response\User\PromotedStudentsResponse;
use App\Core\Application\Mappers\UserMapper;
use App\Core\Domain\Enum\User\UserStatus;
use App\Core\Domain\Repositories\Command\Misc\SemesterPromotionsRepInterface;
use App\Core\Domain\Repositories\Command\User\StudentDetailReInterface;
use App\Core\Domain\Repositories\Command\User\UserRepInterface;
use App\Exceptions\Conflict\PromotionAlreadyExecutedException;
use App\Exceptions\NotAllowed\PromotionNotAllowedException;

class PromoteStudentsUseCase
{
    public function __construct(private StudentDetailReInterface $sdRepo,
    private UserRepInterface $userRepo,
    private SemesterPromotionsRepInterface $promotionRepo )
    {
    }

    public function execute(): PromotedStudentsResponse
    {
        $allowedMonths = config('promotions.allowed_months');
        $currentMonth = now()->month;

        if (! in_array($currentMonth, $allowedMonths)) {
            throw new PromotionNotAllowedException($allowedMonths);
        }

        if ($this->promotionRepo->wasExecutedThisMonth()) {
            throw new PromotionAlreadyExecutedException();
        }
        $incrementCount=$this->sdRepo->incrementSemesterForAll();
        $userIds = $this->sdRepo->getStudentsExceedingSemesterLimit(10);
        $this->userRepo->changeStatus($userIds, UserStatus::BAJA->value);
        $this->promotionRepo->registerExecution();

        return UserMapper::toPromotedStudentsResponse(
            [
                'promotedStudents' => $incrementCount,
                'desactivatedStudents' => count($userIds)
            ]
        );
    }
}
