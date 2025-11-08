<?php

namespace App\Core\Application\Services;

use App\Core\Application\Traits\HasCache;
use App\Core\Application\UseCases\Career\CreateCareerUseCase;
use App\Core\Application\UseCases\Career\DeleteCareerUseCase;
use App\Core\Application\UseCases\Career\FindAllCareersUseCase;
use App\Core\Application\UseCases\Career\FindCareerUseCase;
use App\Core\Application\UseCases\Career\UpdateCareerUseCase;
use App\Core\Domain\Entities\Career;
use App\Core\Infraestructure\Cache\CacheService;

class CareerServiceFacades
{
     use HasCache;

    private string $prefix = 'careers';
    public function __construct(
        private CreateCareerUseCase $create,
        private DeleteCareerUseCase $delete,
        private FindAllCareersUseCase $all,
        private FindCareerUseCase $find,
        private UpdateCareerUseCase $update,
        private CacheService $service
    )
    {
    }

    public function createCareer(Career $career): Career
    {
        $career = $this->create->execute($career);
        $this->service->forget("$this->prefix:all");
        return $career;
    }

    public function deleteCareer(int $careerId): void
    {
        $this->delete->execute($careerId);
        $this->service->forget("$this->prefix:all");
        $this->service->forget("$this->prefix:$careerId");
    }

    public function findAllCareers(bool $forceRefresh): array
    {
        $key = "$this->prefix:all";
        return $this->cache($key, $forceRefresh, fn() => $this->all->execute());
    }

    public function findById(int $id, bool $forceRefresh): Career
    {
        $key = "$this->prefix:$id";
        return $this->cache($key, $forceRefresh, fn() => $this->find->execute($id));    }

    public function updateCareer(int $careerId, array $fields): Career
    {
        $career = $this->update->execute($careerId, $fields);
        $this->service->forget("$this->prefix:all");
        $this->service->forget("$this->prefix:$careerId");
        return $career;
    }
}
