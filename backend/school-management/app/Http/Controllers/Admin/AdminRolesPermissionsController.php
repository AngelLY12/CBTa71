<?php

namespace App\Http\Controllers\Admin;

use App\Core\Application\Mappers\UserMapper;
use App\Core\Application\Services\Admin\AdminRolePermissionsServiceFacades;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\FindPermissionsByCurpsRequest;
use App\Http\Requests\Admin\FindPermissionsByRoleRequest;
use App\Http\Requests\Admin\FindPermissionsToUserRequest;
use App\Http\Requests\Admin\UpdatePermissionsRequest;
use App\Http\Requests\Admin\UpdatePermissionsToUserRequest;
use App\Http\Requests\Admin\UpdateRolesRequest;
use App\Http\Requests\Admin\UpdateRolesToUserRequest;
use App\Http\Requests\General\ForceRefreshRequest;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;

/**
 * @OA\Tag(
 *     name="Admin",
 *     description="Endpoints para gestión administrativa (asignación e importación de usuarios)"
 * )
 */
class AdminRolesPermissionsController extends Controller
{
    private AdminRolePermissionsServiceFacades $service;

    public function __construct(AdminRolePermissionsServiceFacades $service)
    {
        $this->service= $service;
    }

    public function updatePermissionsToUser(UpdatePermissionsToUserRequest $request, int $userId)
    {
        $permissionsToAdd = $request->validated('permissionsToAdd',[]);
        $permissionsToRemove = $request->validated('permissionsToRemove',[]);
        $updated = $this->service->updatePermissionsToUser($userId,$permissionsToAdd, $permissionsToRemove);
        return Response::success(['updated' => $updated], "Permisos actualizados correctamente");
    }
    public function updatePermissions(UpdatePermissionsRequest $request)
    {
        Log::info('=== INICIO updatePermissions ===');
        Log::info('1. Validando request...');
        $validated=$request->validated();
        Log::info('✅ Request validado:', $validated);
        Log::info('2. Creando DTO...');
        $dto = UserMapper::toUpdateUserPermissionsDTO($validated);
        Log::info('✅ DTO creado:', [
            'curps' => $dto->curps,
            'role' => $dto->role,
            'permissionsToAdd' => $dto->permissionsToAdd,
            'permissionsToRemove' => $dto->permissionsToRemove,
            'curps_is_array' => is_array($dto->curps),
            'curps_count' => is_array($dto->curps) ? count($dto->curps) : 'N/A',
            'curps_empty' => empty($dto->curps),
        ]);
        Log::info('3. Ejecutando servicio syncPermissions...');
        try {
            $updated = $this->service->syncPermissions($dto);
            Log::info('✅ Servicio ejecutado exitosamente');
        } catch (\Exception $e) {
            Log::error('❌ Error en servicio:', [
                'message' => $e->getMessage(),
                'code' => $e->getCode(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
        Log::info('=== FIN updatePermissions ===');
        return Response::success(['users_permissions' => $updated], 'Permisos actualizados correctamente.');

    }
    public function updateRolesToUser(UpdateRolesToUserRequest $request, int $userId)
    {
        $rolesToAdd = $request->validated('rolesToAdd',[]);
        $rolesToRemove = $request->validated('rolesToRemove',[]);
        $updated = $this->service->updateRolesToUser($userId, $rolesToAdd, $rolesToRemove);
        return Response::success(['updated' => $updated], 'Roles actualizados correctamente.');
    }

    public function syncRoles(UpdateRolesRequest $request)
    {
        $validated=$request->validated();
        $dto = UserMapper::toUpdateUserRoleDTO($validated);
        $updated=$this->service->syncRoles($dto);

        return Response::success(['users_roles' => $updated], 'Roles actualizados correctamente.');

    }

    public function findPermissionsToUser(FindPermissionsToUserRequest $request, int $userId)
    {
        $roles = $request->validated('roles',[]);
        $forceRefresh = $request->validated('forceRefresh', false);
        $permissions = $this->service->findPermissionsToSingleUser($userId, $roles, $forceRefresh);
        return Response::success(['permissions' => $permissions]);
    }
    public function findAllPermissionsByCurps(FindPermissionsByCurpsRequest $request)
    {
        $curps = $request->validated('curps', []);
        $permissions= $this->service->findAllPermissionsByCurps($curps);
        return Response::success(['permissions' => $permissions]);

    }

    public function findAllPermissionsByRole(FindPermissionsByRoleRequest $request)
    {
        $role = $request->validated('role');
        $forceRefresh = $request->validated('forceRefresh', false);
        $permissions= $this->service->findAllPermissionsByRole($role,$forceRefresh);
        return Response::success(['permissions' => $permissions]);

    }

    public function findAllRoles(ForceRefreshRequest $request)
    {
        $forceRefresh = $request->validated()['forceRefresh'] ?? false;
        $roles= $this->service->findAllRoles($forceRefresh);
        return Response::success(['roles' => $roles]);

    }

    public function findRoleById(int $id)
    {
        $role= $this->service->findRolById($id);
        return Response::success(['role' => $role]);

    }

    public function findPermissionById(int $id)
    {
        $permission= $this->service->findPermissionById($id);
        return Response::success(['permission' => $permission]);

    }
}
