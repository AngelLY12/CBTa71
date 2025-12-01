<?php

namespace App\Core\Infraestructure\Repositories\Command\User;

use App\Core\Application\DTO\Request\User\CreateUserDTO;
use App\Core\Application\DTO\Response\User\UserChangedStatusResponse;
use App\Core\Domain\Entities\User;
use App\Models\User as EloquentUser;
use App\Core\Infraestructure\Mappers\UserMapper;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Core\Application\Mappers\UserMapper as AppUserMapper;
use App\Core\Domain\Enum\User\UserRoles;
use App\Core\Domain\Enum\User\UserStatus;
use App\Core\Domain\Repositories\Command\User\UserRepInterface;
use Illuminate\Support\Collection;

class EloquentUserRepository implements UserRepInterface
{

    public function create(CreateUserDTO $user): User
    {
         $eloquentUser = EloquentUser::create(
            UserMapper::toPersistence($user)
        );
        $eloquentUser->refresh();
        return UserMapper::toDomain($eloquentUser);
    }

    public function update(int $userId, array $fields): User
    {
        $eloquentUser =  $this->findOrFail($userId);
        $eloquentUser->update($fields);
        $eloquentUser->refresh();
        return UserMapper::toDomain($eloquentUser);
    }

    public function createToken(int $userId, string $name): string
    {
        $eloquentUser = $this->findOrFail($userId);
        return $eloquentUser->createToken($name, expiresAt:now()->addMinutes(30))->plainTextToken;
    }

    public function createRefreshToken(int $userId, string $name): string
    {
        $eloquentUser = $this->findOrFail($userId);
        $refreshToken = bin2hex(random_bytes(64));
        $eloquentUser->refreshTokens()->create([
            'token' => hash('sha256', $refreshToken),
            'expires_at' => now()->addDays(7),
            'revoked' => false
        ]);
        return $refreshToken;
    }

    private function findOrFail(int $id):EloquentUser
    {
        return EloquentUser::findOrFail($id);
    }

    public function assignRole(int $userId, string $role): bool
    {
        $user = EloquentUser::find($userId);

        if (! $user) {
            return false;
        }
        $user->assignRole($role);
        if ($role !== UserRoles::UNVERIFIED->value && $user->hasRole(UserRoles::UNVERIFIED->value)) {
            $user->removeRole(UserRoles::UNVERIFIED->value);
        }
        $user->refresh();
        return true;
    }

    public function insertManyUsers(array $usersData): Collection {
        EloquentUser::insert($usersData);
        return EloquentUser::whereIn('email', collect($usersData)->pluck('email'))->get();
    }

    public function deletionEliminateUsers(): int
    {
        $thresholdDate = Carbon::now()->subDays(30);

        return DB::table('users')
            ->where('status', '=', UserStatus::ELIMINADO)
            ->where('updated_at', '<', $thresholdDate)
            ->delete();
    }
    public function changeStatus(array $userIds, string $status): UserChangedStatusResponse
    {
        if (empty($userIds)) {
            return new UserChangedStatusResponse([], $status, 0);
        }

        $affected = EloquentUser::whereIn('id', $userIds)
            ->update(['status' => $status, 'updated_at' => now()]);

        $usersData = [];
        EloquentUser::whereIn('id', $userIds)
            ->where('status', $status)
            ->select(['name', 'last_name', 'curp', 'status'])
            ->chunk(500, function ($usersChunk) use (&$usersData) {
                foreach ($usersChunk as $user) {
                    $usersData[] = [
                        'name'   => "{$user->name} {$user->last_name}",
                        'curp'   => $user->curp,
                        'status' => $user->status
                    ];
                }
            });

        $data = [
            'users'  => $usersData,
            'status' => $status,
            'total'  => $affected
        ];

        return AppUserMapper::toUserChangedStatusResponse($data);
    }



}
