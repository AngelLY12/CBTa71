<?php

namespace App\Core\Infraestructure\Repositories\Command;

use App\Core\Application\DTO\CreateStudentDetail;
use App\Core\Application\DTO\Request\StudentDetail\CreateStudentDetailDTO;
use App\Core\Application\DTO\Request\User\CreateUserDTO;
use App\Core\Application\DTO\Request\User\UpdateUserPermissionsDTO;
use App\Core\Application\DTO\Request\User\UpdateUserRoleDTO;
use App\Core\Application\DTO\Response\User\UserChangedStatusResponse;
use App\Core\Application\DTO\Response\User\UserWithUpdatedRoleResponse;
use App\Core\Application\DTO\Response\User\UserWithUptadedRoleResponse;
use App\Core\Domain\Entities\User;
use App\Core\Domain\Repositories\Command\UserRepInterface;
use App\Models\User as EloquentUser;
use App\Core\Infraestructure\Mappers\UserMapper;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\PersonalAccessToken;
use Spatie\Permission\Models\Permission;
use App\Core\Application\Mappers\UserMapper as AppUserMapper;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role;

class EloquentUserRepository implements UserRepInterface{

    public function create(CreateUserDTO $user): User
    {
         $eloquentUser = EloquentUser::create(
            UserMapper::toPersistence($user)
        );
        $eloquentUser->refresh();
        return UserMapper::toDomain($eloquentUser);
    }

    public function findById(int $userId): User
    {
        $eloquent= $this->findOrFail($userId);
        return UserMapper::toDomain($eloquent);
    }


    public function getUserByStripeCustomer(string $customerId): User
    {
        $user = EloquentUser::where('stripe_customer_id', $customerId)->first();
        if (!$user) {
            logger()->error("Usuario no encontrado: {$customerId}");
            throw new ModelNotFoundException('Usuario no encontrado');
        }
        return UserMapper::toDomain($user);
    }

    public function findUserByEmail(string $email): ?User
    {
        $user=EloquentUser::where('email',$email)->first();
        return $user ? UserMapper::toDomain($user): null;

    }

    public function update(User $user, array $fields): User
    {
        $eloquentUser =  $this->findOrFail($user->id);
        $eloquentUser->update($fields);
        return UserMapper::toDomain($eloquentUser);
    }

    public function createToken(User $user, string $name): string
    {
        $eloquentUser = $this->findOrFail($user->id);
        return $eloquentUser->createToken($name, expiresAt:now()->addMinutes(15))->plainTextToken;
    }

    public function createRefreshToken(User $user, string $name): string
    {
        $eloquentUser = $this->findOrFail($user->id);
        $refreshToken = bin2hex(random_bytes(64));
        $eloquentUser->refreshTokens()->create([
            'token' => hash('sha256', $refreshToken),
            'expires_at' => now()->addDays(7),
            'revoked' => false
        ]);
        return $refreshToken;
    }

    public function revokeToken(string $tokenId): void
    {
        $token = PersonalAccessToken::find($tokenId);

        if ($token) {
            $token->delete();
        }
    }

    public function attachStudentDetail(CreateStudentDetailDTO $detail): User
    {
        $eloquentUser =  $this->findOrFail($detail->user_id);
        $eloquentUser->studentDetail()->updateOrCreate(
            ['user_id' => $detail->user_id],
        [
            'career_id' => $detail->career_id,
            'n_control' => $detail->n_control,
            'semestre'  => $detail->semestre,
            'group'     => $detail->group,
            'workshop'  => $detail->workshop
        ]
    );
        $eloquentUser->load('studentDetail');
        $eloquentUser->assignRole('student');
        return UserMapper::toDomain($eloquentUser);
    }

    public function getUserWithStudentDetail(User $user): User
    {
        $eloquent = $this->findOrFail($user->id);
        $eloquent->load('studentDetail');
        return UserMapper::toDomain($eloquent);
    }

    private function findOrFail(int $id):EloquentUser
    {
        return EloquentUser::findOrFail($id);
    }

    public function bulkInsertWithStudentDetails(array $rows): int
    {
        $totalInserted = DB::transaction(function () use ($rows) {
            $users = [];
            $studentDetails = [];
            foreach ($rows as $row) {
                $users[] = [
                    'name' => $row[0],
                    'last_name' => $row[1],
                    'email' => $row[2],
                    'password' => Hash::make($row[3] ?? 'default123'),
                    'phone_number' => $row[4],
                    'birthdate' => !empty($row[5]) ? Carbon::parse($row[5]) : null,
                    'gender' => $row[6],
                    'curp' => $row[7],
                    'address' => [
                        'street' => $row[8] ?? null,
                        'city' => $row[9] ?? null,
                        'state' => $row[10] ?? null,
                        'zip_code' => $row[11] ?? null,
                    ],
                    'stripe_customer_id' => $row[12] ?? null,
                    'blood_type' => $row[13] ?? null,
                    'registration_date' => !empty($row[14]) ? Carbon::parse($row[14]) : now(),
                    'status' => $row[15] ?? 'activo',
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            EloquentUser::insert($users);

            $insertedUsers = EloquentUser::whereIn('email', collect($users)->pluck('email'))->get();

            foreach ($insertedUsers as $index => $user) {
                $row = $rows[$index];
                $studentDetails[] = [
                    'user_id' => $user->id,
                    'career_id' => $row[16],
                    'n_control' => $row[17],
                    'semestre'  => $row[18],
                    'group'     => $row[19],
                    'workshop'  => $row[20],
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            DB::table('student_details')->insert($studentDetails);

            $roleId = Role::where('name', 'student')->value('id');

            $roleRows = [];
            foreach ($insertedUsers as $user) {
                $roleRows[] = [
                    'role_id' => $roleId,
                    'model_type' => EloquentUser::class,
                    'model_id' => $user->id,
                ];
            }

            DB::table('model_has_roles')->insertOrIgnore($roleRows);
            return $insertedUsers->count();

        });
        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
        return $totalInserted;
    }

    public function updatePermissionToMany(UpdateUserPermissionsDTO $dto): array
    {
        if (!empty($dto->role)) {
            $users = EloquentUser::role($dto->role)->get(['id', 'name', 'last_name', 'curp']);
        } elseif (!empty($dto->curps)) {
            $users = EloquentUser::whereIn('curp', $dto->curps)->get(['id', 'name', 'last_name', 'curp']);
        } else {
            return [];
        }
        if ($users->isEmpty()) {
            return [];
        }
        $permissionsToAddIds=[];
        $permissionsToRemoveIds=[];
        if(!empty($dto->permissionsToAdd)){
            $permissionsToAddIds = Permission::whereIn('name', $dto->permissionsToAdd)->pluck('id')->toArray();
        }
        if(!empty($dto->permissionsToRemove))
        {
            $permissionsToRemoveIds = Permission::whereIn('name', $dto->permissionsToRemove)->pluck('id')->toArray();
        }
        if (empty($permissionsToAddIds) && empty($permissionsToRemoveIds)) {
            return [];
        }

        DB::transaction(function () use ($users, $permissionsToAddIds, $permissionsToRemoveIds) {
            $userIds = $users->pluck('id')->toArray();
            if (!empty($permissionsToRemoveIds)) {
                DB::table('model_has_permissions')
                    ->whereIn('model_id', $userIds)
                    ->whereIn('permission_id', $permissionsToRemoveIds)
                    ->where('model_type', EloquentUser::class)
                    ->delete();
            }

            if (!empty($permissionsToAddIds)) {
                $rows = [];
                foreach ($userIds as $userId) {
                    foreach ($permissionsToAddIds as $permissionId) {
                        $rows[] = [
                            'permission_id' => $permissionId,
                            'model_type' => EloquentUser::class,
                            'model_id' => $userId,
                        ];
                    }
                }

            DB::table('model_has_permissions')->insertOrIgnore($rows);
        }

    });
    app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
        $permissions =[
            'added' => $dto->permissionsToAdd ?? [],
            'removed' => $dto->permissionsToRemove ?? [],
        ];
        $totalUpdated = $users->count();
        if (!empty($dto->role)) {
            return [AppUserMapper::toUserUpdatedPermissionsResponse(permissions:$permissions, role:$dto->role, totalUpdated:$totalUpdated)];
        }

        return $users->map(fn($user) =>AppUserMapper::toUserUpdatedPermissionsResponse(user:$user, permissions:$permissions ,totalUpdated:$totalUpdated))->toArray();
    }

    public function updateRoleToMany(UpdateUserRoleDTO $dto): UserWithUpdatedRoleResponse
    {
        if(empty($dto->curps))
        {
            return new UserWithUpdatedRoleResponse([], [], ['added' => [], 'removed' => []], 0);
        }
        if (empty($dto->rolesToAdd) && empty($dto->rolesToRemove)) {
            return new UserWithUpdatedRoleResponse([], [], ['added' => [], 'removed' => []], 0);
        }
        $users =EloquentUser::whereIn('curp', $dto->curps)->get(['id', 'name', 'last_name', 'curp']);

        $rolesToAddIds=[];
        $rolesToRemoveIds=[];
        if(!empty($dto->rolesToAdd)){
            $rolesToAddIds = Role::whereIn('name', $dto->rolesToAdd)->pluck('id')->toArray();
        }
        if(!empty($dto->rolesToRemove))
        {
            $rolesToRemoveIds = Role::whereIn('name', $dto->rolesToRemove)->pluck('id')->toArray();
        }
        if (empty($rolesToAddIds) && empty($rolesToRemoveIds)) {
            return new UserWithUpdatedRoleResponse([], [], ['added' => [], 'removed' => []], 0);
        }

        DB::transaction(function () use ($users, $rolesToAddIds, $rolesToRemoveIds) {
            $userIds = $users->pluck('id')->toArray();
            if (!empty($rolesToRemoveIds)) {
                DB::table('model_has_roles')
                    ->whereIn('model_id', $userIds)
                    ->whereIn('role_id', $rolesToRemoveIds)
                    ->where('model_type', EloquentUser::class)
                    ->delete();
            }

            if (!empty($rolesToAddIds)) {
                $rows = [];
                foreach ($userIds as $userId) {
                    foreach ($rolesToAddIds as $roleId) {
                        $rows[] = [
                            'role_id' => $roleId,
                            'model_type' => EloquentUser::class,
                            'model_id' => $userId,
                        ];
                    }
                }

            DB::table('model_has_roles')->insertOrIgnore($rows);
        }


    });
        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
        $data =[
            'names' => $users->map(fn($user) => "{$user->name} {$user->last_name}")->toArray(),
            'curps' => $users->pluck('curp')->toArray(),
            'roles' => [
                'added' => $dto->rolesToAdd ?? [],
                'removed' => $dto->rolesToRemove ?? [],
            ],
            'totalUpdated' => $users->count()
        ];

        return AppUserMapper::toUserWithUptadedRoleResponse($data);
    }

    public function deletionInvalidTokens(): int
    {
        $now = Carbon::now();
        $deleted = DB::table('personal_access_tokens')
            ->whereNotNull('expires_at')
            ->where('expires_at', '<', $now)
            ->delete();
        return $deleted;
    }

    public function deletionEliminateUsers(): int
    {
        $thresholdDate = Carbon::now()->subDays(30);

        return DB::table('users')
            ->where('status', '=', 'eliminado')
            ->where('updated_at', '<', $thresholdDate)
            ->delete();
    }
    public function changeStatus(array $userIds,string $status): UserChangedStatusResponse
    {
        if (empty($userIds)) {
            return new UserChangedStatusResponse([], $status, 0);
        }
        $affected = EloquentUser::whereIn('id',$userIds)->update(['status' => $status, 'updated_at' => now()]);
        $users = EloquentUser::whereIn('id', $userIds)
        ->where('status', $status)
        ->get(['name', 'last_name','curp','status']);
        $data =
        [
            'users' => $users->map(fn($user) => [
                'name'=> "{$user->name} {$user->last_name}",
                'curp' => $user->curp,
                'status' => $user->status
                ])->toArray(),
            'status' => $status,
            'total' => $affected
        ];
        return AppUserMapper::toUserChangedStatusResponse($data);
    }

}
