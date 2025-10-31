<?php

namespace App\Imports;

use App\Core\Application\DTO\Request\StudentDetail\CreateStudentDetailDTO;
use App\Core\Application\Services\Admin\AdminService;
use App\Core\Domain\Repositories\Command\UserRepInterface;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
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
