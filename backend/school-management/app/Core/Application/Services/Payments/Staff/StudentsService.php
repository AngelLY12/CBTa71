<?php

namespace App\Core\Application\Services\Payments\Staff;

use App\Core\Application\DTO\Response\General\PaginatedResponse;
use App\Core\Application\UseCases\Payments\Staff\Students\ShowAllStudentsUseCase;

class StudentsService
{
    public function __construct(
        private ShowAllStudentsUseCase $show
    )
    {}

    public function showAllStudents(?string $search=null, int $perPage = 15):PaginatedResponse
    {
        return $this->show->execute($search,$perPage);
    }

}
