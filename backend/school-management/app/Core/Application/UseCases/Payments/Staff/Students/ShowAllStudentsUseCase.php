<?php

namespace App\Core\Application\UseCases\Payments\Staff\Students;

use App\Core\Application\DTO\Response\General\PaginatedResponse;
use App\Core\Application\Mappers\GeneralMapper;
use App\Core\Domain\Repositories\Query\UserQueryRepInterface;

class ShowAllStudentsUseCase
{

    public function __construct(
        private UserQueryRepInterface $uqRepo,
    )
    {

    }
    public function execute(?string $search, int $perPage, $page): PaginatedResponse
    {
        $paginatedStudents = $this->uqRepo->findActiveStudents($search, $perPage, $page);
        $studentIds = $paginatedStudents->getCollection()->pluck('id')->toArray();
        $items = $this->uqRepo->getStudentsWithPendingSummary($studentIds);
        return GeneralMapper::toPaginatedResponse($items, $paginatedStudents);
    }
}
