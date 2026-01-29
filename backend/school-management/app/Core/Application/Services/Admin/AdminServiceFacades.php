<?php

namespace App\Core\Application\Services\Admin;

use App\Core\Application\DTO\Request\StudentDetail\CreateStudentDetailDTO;
use App\Core\Application\DTO\Request\User\CreateUserDTO;
use App\Core\Application\DTO\Request\User\UpdateUserPermissionsDTO;
use App\Core\Application\DTO\Request\User\UpdateUserRoleDTO;
use App\Core\Application\DTO\Response\General\ImportResponse;
use App\Core\Application\DTO\Response\General\PaginatedResponse;
use App\Core\Application\DTO\Response\General\PermissionsByRole;
use App\Core\Application\DTO\Response\General\PermissionsByUsers;
use App\Core\Application\DTO\Response\User\UserChangedStatusResponse;
use App\Core\Application\DTO\Response\User\UserExtraDataResponse;
use App\Core\Application\DTO\Response\User\UserWithUpdatedRoleResponse;
use App\Core\Application\Traits\HasCache;
use App\Core\Application\UseCases\Admin\RolePermissionManagement\FindAllPermissionsByCurpsUseCase;
use App\Core\Application\UseCases\Admin\RolePermissionManagement\FindAllPermissionsByRoleUseCase;
use App\Core\Application\UseCases\Admin\RolePermissionManagement\FindAllRolesUseCase;
use App\Core\Application\UseCases\Admin\RolePermissionManagement\FindPermissionByIdUseCase;
use App\Core\Application\UseCases\Admin\RolePermissionManagement\FindRoleByIdUseCase;
use App\Core\Application\UseCases\Admin\RolePermissionManagement\SyncPermissionsUseCase;
use App\Core\Application\UseCases\Admin\RolePermissionManagement\SyncRoleUseCase;
use App\Core\Application\UseCases\Admin\StudentManagement\AttachStudentDetailUserCase;
use App\Core\Application\UseCases\Admin\StudentManagement\BulkImportStudentDetailsUseCase;
use App\Core\Application\UseCases\Admin\StudentManagement\FindStudentDetailUseCase;
use App\Core\Application\UseCases\Admin\StudentManagement\UpdateStudentDeatilsUseCase;
use App\Core\Application\UseCases\Admin\UserManagement\ActivateUserUseCase;
use App\Core\Application\UseCases\Admin\UserManagement\BulkImportUsersUseCase;
use App\Core\Application\UseCases\Admin\UserManagement\DeleteLogicalUserUseCase;
use App\Core\Application\UseCases\Admin\UserManagement\DisableUserUseCase;
use App\Core\Application\UseCases\Admin\UserManagement\GetExtraUserDataUseCase;
use App\Core\Application\UseCases\Admin\UserManagement\ShowAllUsersUseCase;
use App\Core\Application\UseCases\Admin\UserManagement\TemporaryDisableUserUseCase;
use App\Core\Application\UseCases\User\RegisterUseCase;
use App\Core\Domain\Entities\Permission;
use App\Core\Domain\Entities\Role;
use App\Core\Domain\Entities\StudentDetail;
use App\Core\Domain\Entities\User;
use App\Core\Domain\Enum\Cache\AdminCacheSufix;
use App\Core\Domain\Enum\Cache\CachePrefix;
use App\Core\Domain\Enum\User\UserStatus;
use App\Core\Infraestructure\Cache\CacheService;

//use App\Core\Application\DTO\Response\User\PromotedStudentsResponse;
//use App\Core\Application\UseCases\Jobs\PromoteStudentsUseCase;

class AdminServiceFacades
{
    use HasCache;
    private array $requestCache = [];
    private const TAG_USERS_ALL = [CachePrefix::ADMIN->value, AdminCacheSufix::USERS->value, "all"];
    private const TAG_USERS_ID = [CachePrefix::ADMIN->value, AdminCacheSufix::USERS->value, "all:id"];
    private const TAG_ROLES = [CachePrefix::ADMIN->value, AdminCacheSufix::ROLES->value];
    private const TAG_USER_PROFILE =[CachePrefix::USER->value, "profile"];
    private const TAG_STUDENT_DETAILS = [CachePrefix::USER->value, "student-details"];

    public function __construct(
        private FindStudentDetailUseCase        $find_student,
        private UpdateStudentDeatilsUseCase     $update_student,
        private AttachStudentDetailUserCase     $attach,
        private RegisterUseCase                 $register,
        private BulkImportUsersUseCase           $import,
        private BulkImportStudentDetailsUseCase  $importStudentDetail,
        private SyncPermissionsUseCase           $sync,
        private ShowAllUsersUseCase              $show,
        private GetExtraUserDataUseCase          $extraData,
        private ActivateUserUseCase              $activate,
        private DeleteLogicalUserUseCase         $delete,
        private DisableUserUseCase               $disable,
        private TemporaryDisableUserUseCase      $temporaryDisable,
        private FindAllRolesUseCase              $roles,
        private FindAllPermissionsByCurpsUseCase $permissions,
        private FindAllPermissionsByRoleUseCase      $permissionsByRole,
        private FindRoleByIdUseCase              $role,
        private FindPermissionByIdUseCase        $permission,
        private SyncRoleUseCase                  $syncRoles,
        private CacheService                     $service
    )
    {
        $this->setCacheService($service);
    }
    public function attachStudentDetail(CreateStudentDetailDTO $create): User
    {
        $student=$this->attach->execute($create);
        $this->service->flushTags(array_merge(self::TAG_USERS_ID, ["userId:$student->id"]));
        $this->service->flushTags(array_merge(self::TAG_STUDENT_DETAILS,["userId:$student->id"]));
        return $student;
    }

    public function findStudentDetail(int $user_id): StudentDetail
    {
        return $this->find_student->execute($user_id);
    }

    public function updateStudentDetail(int $user_id, array $fields): User
    {
        $sd= $this->update_student->execute($user_id,$fields);
        $this->service->flushTags(array_merge(self::TAG_USERS_ID, ["userId:$user_id"]));
        $this->service->flushTags(array_merge(self::TAG_STUDENT_DETAILS,["userId:$user_id"]));
        return $sd;
    }

    public function registerUser(CreateUserDTO $user, string $password):User
    {
        $user=$this->register->execute($user, $password);
        $this->service->flushTags(self::TAG_USERS_ALL);
        return $user;
    }

    public function importUsers(array $rows): ImportResponse
    {
        $import=$this->import->execute($rows);
        $this->service->flushTags(self::TAG_USERS_ALL);
        return $import;
    }

    public function importStudents(array $rows): ImportResponse
    {
        $import=$this->importStudentDetail->execute($rows);
        $this->service->flushTags(self::TAG_USERS_ID);
        return $import;
    }
    public function showAllUsers(int $perPage, int $page, bool $forceRefresh, ?UserStatus $status = null): PaginatedResponse
    {
        $statusValue = $status ? $status->value : 'all';
        $key = $this->generateCacheKey(
            CachePrefix::ADMIN->value,
            AdminCacheSufix::USERS->value . ":all",
            ['page' => $page, 'perPage' => $perPage, 'status' => $statusValue]
        );
        return $this->longCache($key, fn() => $this->show->execute($perPage, $page, $status),self::TAG_USERS_ALL,$forceRefresh);
    }

    public function getExtraUserData(int $userId, bool $forceRefresh): UserExtraDataResponse
    {
        $key = $this->generateCacheKey(CachePrefix::ADMIN->value, AdminCacheSufix::USERS->value . ":all:id",["userId" => $userId]);
        return $this->shortCache($key, fn() => $this->extraData->execute($userId), self::TAG_USERS_ID ,$forceRefresh);
    }
    public function syncPermissions(UpdateUserPermissionsDTO $dto):array
    {
        $permissions=$this->sync->execute($dto);
        $this->service->flushTags(self::TAG_USERS_ID);
        return $permissions;
    }

    public function syncRoles(UpdateUserRoleDTO $dto):UserWithUpdatedRoleResponse
    {
        $roles=$this->syncRoles->execute($dto);
        $this->service->flushTags(self::TAG_USERS_ID);
        return $roles;
    }

    public function activateUsers(array $ids): UserChangedStatusResponse
    {
        $users=$this->activate->execute($ids);
        $this->service->flushTags(self::TAG_USERS_ALL);
        return$users;
    }
     public function deleteUsers(array $ids): UserChangedStatusResponse
    {
        $users=$this->delete->execute($ids);
        $this->service->flushTags(self::TAG_USERS_ALL);
        return $users;
    }
     public function disableUsers(array $ids): UserChangedStatusResponse
    {
        $users=$this->disable->execute($ids);
        $this->service->flushTags(self::TAG_USERS_ALL);
        return $users;
    }

    public function temporaryDisableUsers(array $ids): UserChangedStatusResponse
    {
        $users=$this->temporaryDisable->execute($ids);
        $this->service->flushTags(self::TAG_USERS_ALL);
        return $users;
    }

    public function findAllPermissionsByCurps(array $curps): PermissionsByUsers
    {
        $key = implode(',', $curps);
        if (isset($this->requestCache[$key])) {
            return $this->requestCache[$key];
        }
        $permissions = $this->permissions->execute($curps);
        $this->requestCache[$key] = $permissions;

        return $permissions;
    }

    public function findAllPermissionsByRole(string $role): PermissionsByRole
    {
        $key = $role;
        if (isset($this->requestCache[$key])) {
            return $this->requestCache[$key];
        }
        $permissions = $this->permissionsByRole->execute($role);
        $this->requestCache[$key] = $permissions;

        return $permissions;
    }

    public function findAllRoles(bool $forceRefresh): array
    {
        $key=$this->generateCacheKey(CachePrefix::ADMIN->value, AdminCacheSufix::ROLES->value);
        return $this->weeklyCache($key, fn() => $this->roles->execute(), self::TAG_ROLES ,$forceRefresh);
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
