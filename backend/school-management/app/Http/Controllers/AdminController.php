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
use App\Http\Requests\General\PaginationRequest;
use App\Http\Requests\ImportUsersRequest;
use App\Imports\StudentDetailsImport;
use App\Imports\UsersImport;
use Illuminate\Support\Facades\Response;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Str;


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
        $promotion=$this->service->promoteStudentes();
        return Response::success(['affected' => $promotion], 'Se ejecutó la promoción de usuarios correctamente.');

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

    public function import(ImportUsersRequest $request)
    {
        $file= $request->file('file');
        $import=new UsersImport($this->service);
        Excel::import($import,$file);

        return Response::success(['total_imported' => $import->insertedCount], 'Usuarios importados correctamente.');

    }

    public function importStudents(ImportUsersRequest $request)
    {
        $file= $request->file('file');
        $import= new StudentDetailsImport($this->service);
        Excel::import($import,$file);
        return Response::success(['total_imported' => $import->insertedCount], 'Detalles de estudiantes importados correctamente.');
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
        $status=$request->validated()['status'] ?? null;
        $users=$this->service->showAllUsers($perPage, $page,$forceRefresh, $status);
        return Response::success(['users' => $users], 'Usuarios encontrados.');

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
