<?php

namespace App\Core\Application\Services\Payments\Staff;

use App\Core\Application\DTO\Response\General\PaginatedResponse;
use App\Core\Application\Traits\HasCache;
use App\Core\Application\UseCases\Payments\Staff\Students\ShowAllStudentsUseCase;
use App\Core\Domain\Enum\Cache\CachePrefix;
use App\Core\Domain\Enum\Cache\StaffCacheSufix;
use App\Core\Infraestructure\Cache\CacheService;

class StudentsService
{
    use HasCache;
    private const TAG_STUDENTS = [CachePrefix::STAFF->value, StaffCacheSufix::STUDENTS->value, "show"];

    public function __construct(
        private ShowAllStudentsUseCase $show,
        private CacheService $service
    )
    {
        $this->setCacheService($service);

    }

    public function showAllStudents(?string $search, int $perPage, int $page, bool $forceRefresh):PaginatedResponse
    {
        $key = $this->generateCacheKey(
            CachePrefix::STAFF->value,
            StaffCacheSufix::STUDENTS->value . ":show",
            [
                'search' => $search,
                'perPage' => $perPage,
                'page' => $page
            ]
        );
        return $this->mediumCache($key,fn() =>$this->show->execute($search,$perPage, $page),self::TAG_STUDENTS,$forceRefresh );
    }

}
