<?php

namespace App\Core\Application\Services\Admin;

use App\Core\Application\DTO\Request\StudentDetail\CreateStudentDetailDTO;
use App\Core\Application\DTO\Request\User\CreateUserDTO;
use App\Core\Application\DTO\Request\User\UpdateUserPermissionsDTO;
use App\Core\Application\DTO\Request\User\UpdateUserRoleDTO;
use App\Core\Application\DTO\Response\General\PaginatedResponse;
use App\Core\Application\DTO\Response\User\UserChangedStatusResponse;
use App\Core\Application\DTO\Response\User\UserWithUpdatedRoleResponse;
use App\Core\Application\UseCases\Admin\ActivateUserUseCase;
use App\Core\Application\UseCases\Admin\AttachStudentDetailUserCase;
use App\Core\Application\UseCases\Admin\BulkImportUsersUseCase;
use App\Core\Application\UseCases\Admin\DeleteLogicalUserUseCase;
use App\Core\Application\UseCases\Admin\DisableUserUseCase;
use App\Core\Application\UseCases\Admin\FindAllPermissionsUseCase;
use App\Core\Application\UseCases\Admin\FindAllRolesUseCase;
use App\Core\Application\UseCases\Admin\FindPermissionByIdUseCase;
use App\Core\Application\UseCases\Admin\FindRoleByIdUseCase;
use App\Core\Application\UseCases\Admin\ShowAllUsersUseCase;
use App\Core\Application\UseCases\Admin\SyncPermissionsUseCase;
use App\Core\Application\UseCases\Admin\SyncRoleUseCase;
use App\Core\Application\UseCases\RegisterUseCase;
use App\Core\Domain\Entities\Permission;
use App\Core\Domain\Entities\Role;
use App\Core\Domain\Entities\User;

class AdminService
{
    public function __construct(
        private AttachStudentDetailUserCase $attach,
        private RegisterUseCase $register,
        private BulkImportUsersUseCase $import,
        private SyncPermissionsUseCase $sync,
        private ShowAllUsersUseCase $show,
        private ActivateUserUseCase $activate,
        private DeleteLogicalUserUseCase $delete,
        private DisableUserUseCase $disable,
        private FindAllRolesUseCase $roles,
        private FindAllPermissionsUseCase $permissions,
        private FindRoleByIdUseCase $role,
        private FindPermissionByIdUseCase $permission,
        private SyncRoleUseCase $syncRoles
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
    public function showAllUsers(int $perPage, int $page): PaginatedResponse
    {
        return $this->show->execute($perPage,$page);
    }
    public function syncPermissions(UpdateUserPermissionsDTO $dto):array
    {
        return $this->sync->execute($dto);
    }

    public function syncRoles(UpdateUserRoleDTO $dto):UserWithUpdatedRoleResponse
    {
        return $this->syncRoles->execute($dto);
    }

    public function activateUsers(array $ids): UserChangedStatusResponse
    {
        return $this->activate->execute($ids);
    }
     public function deleteUsers(array $ids): UserChangedStatusResponse
    {
        return $this->delete->execute($ids);
    }
     public function disableUsers(array $ids): UserChangedStatusResponse
    {
        return $this->disable->execute($ids);
    }

    public function findAllPermissions(int $page): PaginatedResponse
    {
        return $this->permissions->execute($page);
    }

    public function findAllRoles(): array
    {
        return $this->roles->execute();
    }

    public function findPermissionById(int $id): Permission
    {
        return $this->permission->execute($id);
    }
    public function findRolById(int $id): Role
    {
        return $this->role->execute($id);
    }
}
