<?php

namespace App\Core\Infraestructure\Repositories\Query;

use App\Core\Application\DTO\Response\User\UserIdListDTO;
use App\Core\Domain\Repositories\Query\UserQueryRepInterface;

use App\Core\Application\Mappers\UserMapper as MappersUserMapper;
use App\Models\User as EloquentUser;
use App\Core\Infraestructure\Mappers\UserMapper;
use App\Core\Domain\Entities\PaymentConcept;
use App\Core\Domain\Entities\User;
use Illuminate\Pagination\LengthAwarePaginator;

class EloquentUserQueryRepository implements UserQueryRepInterface
{
    public function getUserIdsByControlNumbers(array $controlNumbers): UserIdListDTO
    {
        $ids = EloquentUser::whereHas('studentDetail', function ($q) use ($controlNumbers) {
            $q->whereIn('n_control', $controlNumbers);
        })
        ->where('status', 'activo')
        ->pluck('id')
        ->toArray();

        return MappersUserMapper::toUserIdListDTO($ids);
    }

    public function countStudents(bool $onlyThisYear = false): int
    {
        $students = EloquentUser::role('student')->where('status','activo');
        if($onlyThisYear){
            $students->whereYear('created_at',now()->year);
        }
        return $students->count();

    }

    public function findBySearch(string $search): ?User
    {
        $user = EloquentUser::with('studentDetail')
            ->where(function ($q) use ($search) {
            $q->where('curp', 'like', "%$search%")
              ->orWhere('email', 'like', "%$search%")
              ->orWhereHas('studentDetail', function($q2) use ($search) {
                  $q2->where('n_control', 'like', "%$search%");
              });
        })
        ->first();
        return $user ? UserMapper::toDomain($user) : null;
    }

    public function findActiveStudents(?string $search, int $perPage = 15): LengthAwarePaginator
    {
       $studentsQuery = EloquentUser::role('student')->select('id','name','last_name','email')
        ->where('status','activo')
        ->with('studentDetail:user_id,semestre,career_id');

        if ($search) {
            $studentsQuery->where(function($q) use ($search) {
                $q->where('curp', 'like', "%$search%")
                ->orWhere('email', 'like', "%$search%")
                ->orWhereHas('studentDetail', function($q2) use ($search) {
                  $q2->where('n_control', 'like', "%$search%");
              });
            });
        }
        return $studentsQuery->paginate($perPage);
    }

    public function getRecipients(PaymentConcept $concept, string $appliesTo): array
    {
        $usersQuery = EloquentUser::query()->where('status', 'activo')->select('id', 'name', 'last_name', 'email');

        $usersQuery = match($appliesTo) {
            'carrera' => $usersQuery->whereHas('studentDetail', function($q) use ($concept) {
                $q->whereIn('career_id', $concept->getCareerIds());
            }),
            'semestre' => $usersQuery->whereHas('studentDetail', function($q) use ($concept) {
                $q->whereIn('semester', $concept->getSemesters());
            }),
            'estudiantes' => $usersQuery->whereIn('id', $concept->getUserIds()),
            'todos' => $usersQuery,
        };

        $users = $usersQuery->get();
        return $users->map(fn($u) => MappersUserMapper::toRecipientDTO($u->toArray()))->toArray();
    }

    public function hasRole(User $user, string $role): bool
    {
        $eloquentUser = EloquentUser::find($user->id);
        return $eloquentUser ? $eloquentUser->hasRole($role) : false;
    }
}
