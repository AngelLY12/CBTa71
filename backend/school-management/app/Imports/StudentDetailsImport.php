<?php

namespace App\Imports;

use App\Core\Application\Services\Admin\AdminServiceFacades;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

class StudentDetailsImport implements ToCollection, ShouldQueue
{
    protected AdminServiceFacades $adminService;
    public int $insertedCount = 0;

    public function __construct(AdminServiceFacades $adminService)
    {
        $this->adminService = $adminService;
    }
    /**
    * @param Collection $collection
    */

    public function collection(Collection $collection)
    {
        $rows = $collection->skip(1)->toArray();
        $this->insertedCount = $this->adminService->importStudents($rows);
    }
}
