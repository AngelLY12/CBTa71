<?php

namespace App\Imports;

use App\Core\Application\Services\Admin\AdminService;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

class UsersImport implements ToCollection
{
    protected AdminService $adminService;

    public function __construct(AdminService $adminService)
    {
        $this->adminService = $adminService;
    }

    public function collection(Collection $collection)
    {
        $rows = $collection->skip(1)->toArray();
        $this->adminService->importUsers($rows);
    }
}
