<?php

namespace App\Core\Infraestructure\Repositories\Command;

use App\Core\Application\DTO\CreateStudentDetail;
use App\Core\Application\DTO\Request\StudentDetail\CreateStudentDetailDTO;
use App\Core\Domain\Entities\User;
use App\Core\Domain\Repositories\Command\UserRepInterface;
use App\Models\User as EloquentUser;
use App\Core\Infraestructure\Mappers\UserMapper;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\PersonalAccessToken;

class EloquentUserRepository implements UserRepInterface{

    public function create(User $user): User
    {
         $eloquentUser = EloquentUser::create(
            UserMapper::toPersistence($user)
        );

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
        return $eloquentUser->createToken($name)->plainTextToken;
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

    public function bulkInsertWithStudentDetails(array $rows): void
    {
        DB::transaction(function () use ($rows) {
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

            foreach ($insertedUsers as $user) {
                $user->assignRole('student');
            }
        });


    }

}
