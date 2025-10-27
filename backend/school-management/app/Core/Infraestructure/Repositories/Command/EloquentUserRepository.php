<?php

namespace App\Core\Infraestructure\Repositories\Command;

use App\Core\Application\DTO\CreateStudentDetail;
use App\Core\Application\DTO\Request\StudentDetail\CreateStudentDetailDTO;
use App\Core\Domain\Entities\User;
use App\Core\Domain\Repositories\Command\UserRepInterface;
use App\Models\User as EloquentUser;
use App\Core\Infraestructure\Mappers\UserMapper;
use Illuminate\Database\Eloquent\ModelNotFoundException;

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
        return $eloquentUser = EloquentUser::findOrFail($id);
    }

}
