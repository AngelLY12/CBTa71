<?php

namespace App\Core\Application\Services\Payments\Staff;

use App\Core\Application\DTO\Response\General\PaginatedResponse;
use App\Core\Application\Traits\HasCache;
use App\Core\Application\UseCases\Payments\Staff\Students\ShowAllStudentsUseCase;
use App\Core\Infraestructure\Cache\CacheService;

class StudentsService
{
    use HasCache;
    public function __construct(
        private ShowAllStudentsUseCase $show,
        private CacheService $cache
    )
    {}

    public function showAllStudents(?string $search, int $perPage, int $page, bool $forceRefresh):PaginatedResponse
    {
        $key = "staff:students:show:$search:$perPage:$page";
        return $this->cache($key,$forceRefresh ,fn() =>$this->show->execute($search,$perPage, $page));
    }

}
