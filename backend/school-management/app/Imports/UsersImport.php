<?php

namespace App\Imports;

use App\Core\Application\DTO\Response\General\ImportResponse;
use App\Core\Application\Services\Admin\AdminServiceFacades;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

class UsersImport implements ToCollection, ShouldQueue
{
    protected AdminServiceFacades $adminService;
    private array $importResult = [];

    public function __construct(AdminServiceFacades $adminService)
    {
        $this->adminService = $adminService;
    }

    public function collection(Collection $collection)
    {
        $rows = $collection->skip(1)->toArray();
        $importResponse = $this->adminService->importUsers($rows);
        $this->importResult = $importResponse->toArray();
    }
    public function getImportResult(): array
    {
        return $this->importResult;
    }
}
