<?php

namespace App\Core\Application\Services\Admin;

use App\Core\Application\DTO\Request\StudentDetail\CreateStudentDetailDTO;
use App\Core\Application\DTO\Request\User\CreateUserDTO;
use App\Core\Application\DTO\Request\User\UpdateUserPermissionsDTO;
use App\Core\Application\DTO\Request\User\UpdateUserRoleDTO;
use App\Core\Application\DTO\Response\General\PaginatedResponse;
use App\Core\Application\DTO\Response\User\UserChangedStatusResponse;
use App\Core\Application\DTO\Response\User\UserWithUpdatedRoleResponse;
use App\Core\Application\Traits\HasCache;
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
use App\Core\Infraestructure\Cache\CacheService;

class AdminService
{
    use HasCache;

    private string $prefix = 'admin';
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
        private SyncRoleUseCase $syncRoles,
        private CacheService $service
    )
    {}
    public function attachStudentDetail(CreateStudentDetailDTO $create): User
    {
        $student=$this->attach->execute($create);
        $this->service->clearPrefix("$this->prefix:users:all");
        $this->service->clearPrefix("$this->prefix:user:$student->id");
        return $student;
    }

    public function registerUser(CreateUserDTO $user, string $password):User
    {
        $user=$this->register->execute($user, $password);
        $this->service->clearPrefix("$this->prefix:users:all");
        return $user;
    }

    public function importUsers(array $rows):int
    {
        $import=$this->import->execute($rows);
        $this->service->clearPrefix("$this->prefix:users:all");
        return $import;
    }
    public function showAllUsers(int $perPage, int $page, bool $forceRefresh): PaginatedResponse
    {
        $key = "$this->prefix:users:page:$page:$perPage";
        return $this->cache($key, $forceRefresh, fn() => $this->show->execute($perPage, $page));
    }
    public function syncPermissions(UpdateUserPermissionsDTO $dto):array
    {
        $permissions=$this->sync->execute($dto);
        $this->service->clearPrefix("$this->prefix:users:all");
        return $permissions;
    }

    public function syncRoles(UpdateUserRoleDTO $dto):UserWithUpdatedRoleResponse
    {
        $roles=$this->syncRoles->execute($dto);
        $this->service->clearPrefix("$this->prefix:users:all");
        return $roles;
    }

    public function activateUsers(array $ids): UserChangedStatusResponse
    {
        $users=$this->activate->execute($ids);
        $this->service->clearPrefix("$this->prefix:users:all");
        return$users;
    }
     public function deleteUsers(array $ids): UserChangedStatusResponse
    {
        $users=$this->delete->execute($ids);
        $this->service->clearPrefix("$this->prefix:users:all");
        return $users;
    }
     public function disableUsers(array $ids): UserChangedStatusResponse
    {
        $users=$this->disable->execute($ids);
        $this->service->clearPrefix("$this->prefix:users:all");
        return $users;
    }

    public function findAllPermissions(string $roleName, bool $forceRefresh = false): array
    {
        $key = "$this->prefix:permissions:role:$roleName";
        return $this->cache($key, $forceRefresh, fn() => $this->permissions->execute($roleName));
    }

    public function findAllRoles(bool $forceRefresh): array
    {
        $key = "$this->prefix:roles";
        return $this->cache($key, $forceRefresh, fn() => $this->roles->execute());
    }

    public function findPermissionById(int $id): Permission
    {
        return  $this->permission->execute($id);
    }
    public function findRolById(int $id): Role
    {
        return $this->role->execute($id);
    }
}
