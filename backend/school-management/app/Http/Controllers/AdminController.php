<?php

namespace App\Http\Controllers;

use App\Core\Application\Mappers\StudentDetailMapper;
use App\Core\Application\Mappers\UserMapper;
use App\Core\Application\Services\Admin\AdminServiceFacades;
use App\Core\Domain\Enum\User\UserStatus;
use App\Http\Requests\Admin\AttachStudentRequest;
use App\Http\Requests\Admin\ChangeUserStatusRequest;
use App\Http\Requests\Admin\FindPermissionsRequest;
use App\Http\Requests\Admin\RegisterUserRequest;
use App\Http\Requests\Admin\ShowUsersPaginationRequest;
use App\Http\Requests\Admin\UpdatePermissionsRequest;
use App\Http\Requests\Admin\UpdateRolesRequest;
use App\Http\Requests\Admin\UpdateStudentRequest;
use App\Http\Requests\General\ForceRefreshRequest;
use App\Http\Requests\General\ImportRequest;
use App\Imports\StudentDetailsImport;
use App\Imports\UsersImport;
use App\Jobs\PromoteStudentsJob;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;


/**
 * @OA\Tag(
 *     name="Admin",
 *     description="Endpoints para gestión administrativa (asignación e importación de usuarios)"
 * )
 */
class AdminController extends Controller
{
    private AdminServiceFacades $service;

    public function __construct(AdminServiceFacades $service)
    {
        $this->service= $service;
    }

    public function registerUser(RegisterUserRequest $request)
    {
        $data = $request->validated();
        $password= Str::random(12);
        $data['password'] = $password;
        $createUser = UserMapper::toCreateUserDTO($data);

        $user = $this->service->registerUser($createUser, $password);

        return Response::success(['user' => $user], 'El usuario ha sido creado con éxito.',201);

    }

    public function promotionStudents()
    {
        PromoteStudentsJob::dispatch(Auth::id())->onQueue('maintenance-heavy');
        return Response::success(null, 'Promoción de estudiantes iniciada en segundo plano.');

    }

    public function attachStudent(AttachStudentRequest $request)
    {
        $data= $request->validated();
        $attachUser = StudentDetailMapper::toCreateStudentDetailDTO($data);

        $user = $this->service->attachStudentDetail($attachUser);

        return Response::success(['user' => $user], 'Se asociaron correctamente los datos al estudiante.', 201);


    }

    public function findStudentDetail(int $id)
    {
        $details= $this->service->findStudentDetail($id);
        return Response::success(['student_details' => $details]);
    }

    public function updateStudentDetail(UpdateStudentRequest $request, int $id)
    {
        $data=$request->validated();
        $userUpdate= $this->service->updateStudentDetail($id,$data);
        return Response::success(['user' => $userUpdate], 'Se actualizaron correctamente los detalles de estudiante.');

    }

    public function import(ImportRequest $request)
    {
        $file= $request->file('file');
        $import=new UsersImport($this->service, Auth::user());
        Excel::queueImport($import,$file)->onQueue('imports');
        return Response::success(null, 'Usuarios procesandose, se te notificara cuando termine.');

    }

    public function importStudents(ImportRequest $request)
    {
        $file= $request->file('file');
        $import= new StudentDetailsImport($this->service, Auth::user());
        Excel::queueImport($import,$file)->onQueue('imports');
        return Response::success(null, 'Usuarios procesandose, se te notificara cuando termine.');
    }

    public function updatePermissions(UpdatePermissionsRequest $request)
    {
        $validated=$request->validated();
        $dto = UserMapper::toUpdateUserPermissionsDTO($validated);
        $updated=$this->service->syncPermissions($dto);

        return Response::success(['users_permissions' => $updated], 'Permisos actualizados correctamente.');

    }

    public function index(ShowUsersPaginationRequest $request)
    {
        $forceRefresh = $request->boolean('forceRefresh');
        $perPage = $request->integer('perPage', 15);
        $page = $request->integer('page', 1);
        $statusStr=$request->validated()['status'] ?? null;
        $status = $statusStr ? UserStatus::tryFrom($statusStr) : null;
        $users=$this->service->showAllUsers($perPage, $page,$forceRefresh, $status);
        return Response::success(['users' => $users], 'Usuarios encontrados.');

    }

    public function getExtraUserData(ForceRefreshRequest $request, int $id)
    {
        $forceRefresh = $request->boolean('forceRefresh');
        $user = $this->service->getExtraUserData($id, $forceRefresh);
        return Response::success(['user' => $user], 'Datos extra de usuario encontrados.');
    }

    public function syncRoles(UpdateRolesRequest $request)
    {
        $validated=$request->validated();
        $dto = UserMapper::toUpdateUserRoleDTO($validated);
        $updated=$this->service->syncRoles($dto);

        return Response::success(['users_roles' => $updated], 'Roles actualizados correctamente.');

    }

    public function activateUsers(ChangeUserStatusRequest $request)
    {
        $ids = $request->validated()['ids'];
        $updated=$this->service->activateUsers($ids);

        return Response::success(['activate_users' => $updated], 'Estatus de usuarios actualizados correctamente.');

    }

    public function deleteUsers(ChangeUserStatusRequest $request)
    {
        $ids = $request->validated()['ids'];
        $updated=$this->service->deleteUsers($ids);

        return Response::success(['delete_users' => $updated], 'Estatus de usuarios actualizados correctamente.');

    }

    public function disableUsers(ChangeUserStatusRequest $request)
    {
        $ids = $request->validated()['ids'];
        $updated=$this->service->disableUsers($ids);

        return Response::success(['disable_users' => $updated], 'Estatus de usuarios actualizados correctamente.');

    }

    public function temporaryDisableUsers(ChangeUserStatusRequest $request)
    {
        $ids = $request->validated()['ids'];
        $updated=$this->service->temporaryDisableUsers($ids);

        return Response::success(['temporary_disable_users' => $updated], 'Estatus de usuarios actualizados correctamente.');

    }

    public function findAllPermissions(FindPermissionsRequest $request)
    {
        $validated = $request->validated();
        $curps = $validated['curps'] ?? [];
        $role  = $validated['role'] ?? null;
        $permissions= $this->service->findAllPermissions($curps, $role);
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
