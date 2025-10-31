<?php

namespace App\Core\Infraestructure\Repositories\Query;

use App\Core\Application\DTO\Response\User\UserIdListDTO;
use App\Core\Domain\Repositories\Query\UserQueryRepInterface;

use App\Core\Application\Mappers\UserMapper as MappersUserMapper;
use App\Models\User as EloquentUser;
use App\Core\Infraestructure\Mappers\UserMapper;
use App\Core\Domain\Entities\PaymentConcept;
use App\Core\Domain\Entities\User;
use App\Core\Infraestructure\Repositories\Traits\HasPendingQuery;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class EloquentUserQueryRepository implements UserQueryRepInterface
{
    use HasPendingQuery;

    public function getUserIdsByControlNumbers(array $controlNumbers): UserIdListDTO
    {
        $ids = EloquentUser::whereHas('studentDetail', fn($q) => $q->whereIn('n_control', $controlNumbers))
        ->where('status', 'activo')
        ->pluck('id')
        ->toArray();

        return MappersUserMapper::toUserIdListDTO($ids);
    }

    public function countStudents(bool $onlyThisYear): int
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

    public function findActiveStudents(?string $search, int $perPage, int $page): LengthAwarePaginator
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
        return $studentsQuery->paginate($perPage, ['*'], 'page', $page);
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

        $recipients = [];
        $usersQuery->chunk(100, function($users) use (&$recipients) {
            foreach ($users as $user) {
                $recipients[] = MappersUserMapper::toRecipientDTO($user->toArray());
            }
        });
        return $recipients;
    }

    public function hasRole(User $user, string $role): bool
    {
        $eloquentUser = EloquentUser::find($user->id);
        return $eloquentUser ? $eloquentUser->hasRole($role) : false;
    }

    public function getStudentsWithPendingSummary(array $userIds): array
    {
        if (empty($userIds)) return [];

        $rows = $this->basePendingQuery($userIds)
            ->leftJoin('student_details', 'student_details.user_id', '=', 'users.id')
            ->leftJoin('careers', 'careers.id', '=', 'student_details.career_id')
            ->selectRaw("
                users.id AS user_id,
                CONCAT(users.name, ' ', users.last_name) AS full_name,
                student_details.semestre AS semestre,
                careers.career_name AS career,
                COUNT(payment_concepts.id) AS total_count,
                COALESCE(SUM(payment_concepts.amount), 0) AS total_amount
            ")
            ->groupBy('users.id', 'users.name', 'users.last_name', 'student_details.semestre', 'careers.career_name')
            ->get();

        return $rows->map(fn($r) => MappersUserMapper::toUserWithPendingSummaryResponse([
            'user_id' => (int)$r->user_id,
            'name' => $r->full_name,
            'semestre' => $r->semestre,
            'career' => $r->career ?? null,
            'total_count' => (int)$r->total_count,
            'total_amount' => (float)$r->total_amount,
        ]))->toArray();
    }
}
