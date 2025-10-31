<?php

namespace App\Core\Application\Services\Admin;

use App\Core\Application\DTO\Request\StudentDetail\CreateStudentDetailDTO;
use App\Core\Application\DTO\Request\User\CreateUserDTO;
use App\Core\Application\UseCases\Admin\AttachStudentDetailUserCase;
use App\Core\Application\UseCases\Admin\BulkImportUsersUseCase;
use App\Core\Application\UseCases\RegisterUseCase;
use App\Core\Domain\Entities\User;

class AdminService
{
    public function __construct(
        private AttachStudentDetailUserCase $attach,
        private RegisterUseCase $register,
        private BulkImportUsersUseCase $import
    )
    {}
    public function attachStudentDetail(CreateStudentDetailDTO $create): User
    {
        return $this->attach->execute($create);
    }

    public function registerUser(CreateUserDTO $user):User
    {
        return $this->register->execute($user);
    }

    public function importUsers(array $rows):void
    {
        $this->import->execute($rows);
    }

}
