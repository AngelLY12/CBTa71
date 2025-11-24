<?php

namespace App\Core\Infraestructure\Repositories\Command;

use App\Core\Application\DTO\Request\StudentDetail\CreateStudentDetailDTO;
use App\Core\Application\DTO\Request\User\CreateUserDTO;
use App\Core\Application\DTO\Response\User\UserChangedStatusResponse;
use App\Core\Domain\Entities\User;
use App\Core\Domain\Repositories\Command\UserRepInterface;
use App\Models\User as EloquentUser;
use App\Core\Infraestructure\Mappers\UserMapper;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Core\Application\Mappers\UserMapper as AppUserMapper;
use App\Core\Domain\Enum\User\UserStatus;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Str;

class EloquentUserRepository implements UserRepInterface{

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

    private function findOrFail(int $id):EloquentUser
    {
        return EloquentUser::findOrFail($id);
    }

    public function bulkInsertWithStudentDetails(array $rows): array
    {
        $createdUsers=[];
        $totalInserted = DB::transaction(function () use ($rows, $createdUsers) {
            $tempPasswords = [];
            $users = [];
            $studentDetails = [];
            $roleRows = [];
            foreach ($rows as $row) {
                $tempPassword = Str::random(12);
                $tempPasswords[] = $tempPassword;
                $users[] = [
                    'name' => $row[0],
                    'last_name' => $row[1],
                    'email' => $row[2],
                    'password' => Hash::make($tempPassword),
                    'phone_number' => $row[3],
                    'birthdate' => !empty($row[4]) ? Carbon::parse($row[4]) : null,
                    'gender' => $row[5] ?? null,
                    'curp' => $row[6],
                    'address' => [
                        'street' => $row[7] ?? null,
                        'city' => $row[8] ?? null,
                        'state' => $row[9] ?? null,
                        'zip_code' => $row[10] ?? null,
                    ],
                    'stripe_customer_id' => $row[11] ?? null,
                    'blood_type' => $row[12] ?? null,
                    'registration_date' => !empty($row[13]) ? Carbon::parse($row[13]) : now(),
                    'status' => $row[14] ?? UserStatus::ACTIVO,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            EloquentUser::insert($users);

            $insertedUsers = EloquentUser::whereIn('email', collect($users)->pluck('email'))->get();
            $roleId = Role::where('name', 'student')->value('id');

            foreach ($insertedUsers as $index => $user) {
                $row = $rows[$index];

                $hasStudentDetails =
                !empty($row[16]) &&
                !empty($row[17]) &&
                !empty($row[18]);

                if($hasStudentDetails)
                {
                    $studentDetails[] = [
                        'user_id' => $user->id,
                        'career_id' => $row[16],
                        'n_control' => $row[17],
                        'semestre'  => $row[18],
                        'group'     => $row[19] ?? null,
                        'workshop'  => $row[20] ?? null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                    $roleRows[] = [
                        'role_id' => $roleId,
                        'model_type' => EloquentUser::class,
                        'model_id' => $user->id,
                    ];
                }
                $createdUsers[] = [
                    'user' => UserMapper::toDomain($user),
                    'password' => $tempPasswords[$index]
                ];
            }

            if (!empty($studentDetails)) {
                DB::table('student_details')->insert($studentDetails);
            }

            if (!empty($roleRows)) {
                DB::table('model_has_roles')->insertOrIgnore($roleRows);
            }

            return [
                'affected'=>$insertedUsers->count(),
                'users'=>$createdUsers
            ];

        });
        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
        return $totalInserted;
    }

    public function deletionEliminateUsers(): int
    {
        $thresholdDate = Carbon::now()->subDays(30);

        return DB::table('users')
            ->where('status', '=', UserStatus::ELIMINADO)
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
