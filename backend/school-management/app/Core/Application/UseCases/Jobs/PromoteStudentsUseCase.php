<?php

namespace App\Core\Application\UseCases\Jobs;

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

    public function execute(): array
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
        $studentsExceedingSemesterLimit = $this->sdRepo->getStudentsExceedingSemesterLimit(12);
        $userIds = $studentsExceedingSemesterLimit->pluck('user_id');
        $this->userRepo->changeStatus($userIds, UserStatus::BAJA->value);
        $this->promotionRepo->registerExecution();
        return [
            'usuarios_promovidos' => $incrementCount,
            'usuarios_baja' => $userIds->count()
        ];
    }
}
