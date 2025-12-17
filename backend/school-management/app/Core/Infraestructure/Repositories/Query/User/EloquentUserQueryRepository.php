<?php

namespace App\Core\Infraestructure\Repositories\Query\User;

use App\Core\Application\DTO\Response\User\UserIdListDTO;
use App\Core\Application\Mappers\UserMapper as MappersUserMapper;
use App\Core\Domain\Enum\Payment\PaymentStatus;
use App\Core\Domain\Enum\PaymentConcept\PaymentConceptAppliesTo;
use App\Models\User as EloquentUser;
use App\Core\Infraestructure\Mappers\UserMapper;
use App\Core\Domain\Entities\PaymentConcept;
use App\Core\Domain\Entities\User;
use App\Core\Domain\Enum\User\UserRoles;
use App\Core\Domain\Enum\User\UserStatus;
use App\Core\Domain\Repositories\Query\User\UserQueryRepInterface;
use App\Core\Infraestructure\Mappers\RolesAndPermissionMapper;
use App\Core\Infraestructure\Traits\HasPendingQuery;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class EloquentUserQueryRepository implements UserQueryRepInterface
{
    use HasPendingQuery;

    public function findById(int $userId): ?User
    {
        return optional(EloquentUser::find($userId),fn($eloquent)=>UserMapper::toDomain($eloquent));
    }

    public function findUserRoles(int $userId): array
    {
        $user = EloquentUser::findOrFail($userId);
        return $user->roles
        ->map(fn($role) => RolesAndPermissionMapper::toRoleDomain($role))
        ->toArray();

    }
    public function getUserWithStudentDetail(int $userId): User
    {
        $eloquent = EloquentUser::findOrFail($userId);
        $eloquent->load('studentDetail');
        return UserMapper::toDomain($eloquent);
    }

    public function getUserByStripeCustomer(string $customerId): User
    {
        $user = EloquentUser::where('stripe_customer_id', $customerId)->first();
        if (!$user) {
            throw new ModelNotFoundException('Usuario no encontrado');
        }
        return UserMapper::toDomain($user);
    }

    public function findUserByEmail(string $email): ?User
    {
        $user=EloquentUser::where('email',$email)->first();
        return $user ? UserMapper::toDomain($user): null;

    }

    public function getUserIdsByControlNumbers(array $controlNumbers): UserIdListDTO
    {
        $ids = EloquentUser::whereHas('studentDetail', fn($q) => $q->whereIn('n_control', $controlNumbers))
        ->where('status', UserStatus::ACTIVO)
        ->pluck('id')
        ->toArray();

        return MappersUserMapper::toUserIdListDTO($ids);
    }

    public function countStudents(bool $onlyThisYear): int
    {
        $students = EloquentUser::role(UserRoles::STUDENT->value)->where('status', UserStatus::ACTIVO);
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
       $studentsQuery = EloquentUser::role(UserRoles::STUDENT->value)->select('id','name','last_name','email')
        ->where('status', UserStatus::ACTIVO)
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
        $usersQuery = EloquentUser::query()->where('status', UserStatus::ACTIVO)->select('id', 'name', 'last_name', 'email');

        $usersQuery = match($appliesTo) {
            PaymentConceptAppliesTo::CARRERA->value => $usersQuery->whereHas('studentDetail', function($q) use ($concept) {
                $q->whereIn('career_id', $concept->getCareerIds());
            }),
            PaymentConceptAppliesTo::SEMESTRE->value => $usersQuery->whereHas('studentDetail', function($q) use ($concept) {
                $q->whereIn('semester', $concept->getSemesters());
            }),
            PaymentConceptAppliesTo::CARRERA_SEMESTRE->value => $usersQuery->whereHas('studentDetail', function($q) use ($concept){
                $q->whereIn('career_id', $concept->getCareerIds())
                ->whereIn('semester', $concept->getSemesters());
            }),
            PaymentConceptAppliesTo::ESTUDIANTES->value => $usersQuery->whereIn('id', $concept->getUserIds()),
            PaymentConceptAppliesTo::TODOS->value => $usersQuery,
            PaymentConceptAppliesTo::TAG->value => $usersQuery->whereHas('applicantTypes', function($q) use ($concept) {
                $q->whereIn('tag', $concept->getApplicantTag());
            }),
        };

        $exceptionIds = $concept->getExceptionUsersIds();
        if (!empty($exceptionIds)) {
            $usersQuery->whereNotIn('id', $exceptionIds);
        }

        $recipients = [];
        $usersQuery->chunk(100, function($users) use (&$recipients) {
            foreach ($users as $user) {
                $recipients[] = MappersUserMapper::toRecipientDTO($user->toArray());
            }
        });
        return $recipients;
    }

    public function hasRole(int $userId, string $role): bool
    {
        $eloquentUser = EloquentUser::find($userId);
        return $eloquentUser ? $eloquentUser->hasRole($role) : false;
    }

    public function getStudentsWithPendingSummary(array $userIds): array
    {
        if (empty($userIds)) return [];

        $paidTotals = DB::table('payments')
            ->select(
                'payments.user_id',
                DB::raw('COALESCE(SUM(payments.amount_received), 0) AS total_paid'),
                DB::raw('COUNT(payments.id) AS total_paid_concepts')
            )
            ->whereIn('payments.status', [
                PaymentStatus::SUCCEEDED->value,
                PaymentStatus::OVERPAID->value,
            ])
            ->whereIn('payments.user_id', $userIds)
            ->groupBy('payments.user_id');

        $rows = $this->basePendingLeftJoinQuery($userIds)
            ->leftJoin('student_details', 'student_details.user_id', '=', 'users.id')
            ->leftJoin('careers', 'careers.id', '=', 'student_details.career_id')
            ->leftJoinSub(
                $paidTotals,
                'paid_totals',
                'paid_totals.user_id',
                '=',
                'users.id'
            )
            ->selectRaw("
                users.id AS user_id,
                CONCAT(users.name, ' ', users.last_name) AS full_name,
                student_details.semestre AS semestre,
                careers.career_name AS career,
                COUNT(pending_concepts.id) AS total_count,
                COALESCE(SUM(pending_concepts.amount), 0) AS total_amount,
                COALESCE(
                    SUM(CASE WHEN pending_concepts.is_expired = 1 THEN 1 ELSE 0 END),
                    0
                ) AS expired_count,
                COALESCE(
                    SUM(
                        CASE
                            WHEN pending_concepts.is_expired = 1
                            THEN pending_concepts.amount
                            ELSE 0
                        END
                    ),
                    0
                ) AS expired_amount,
                COALESCE(paid_totals.total_paid, 0) AS total_paid,
                COALESCE(paid_totals.total_paid_concepts, 0) AS total_paid_concepts
            ")
            ->groupBy(
                'users.id',
                'users.name',
                'users.last_name',
                'student_details.semestre',
                'careers.career_name',
                'paid_totals.total_paid',
                'paid_totals.total_paid_concepts'
            )
            ->get();

        return $rows->map(fn($r) => MappersUserMapper::toUserWithPendingSummaryResponse([
            'user_id'      => (int)$r->user_id,
            'name'         => $r->full_name,
            'semestre'     => $r->semestre,
            'career'       => $r->career ?? null,
            'total_count'  => (int)$r->total_count,
            'total_amount' => $r->total_amount,
            'expired_count' => (int) $r->expired_count,
            'expired_amount' => $r->expired_amount,
            'total_paid_concepts' => (int) $r->total_paid_concepts,
            'total_paid'     => $r->total_paid,
        ]))->toArray();
    }

    public function findAllUsers(int $perPage, int $page): LengthAwarePaginator
    {
        $paginator = EloquentUser::with([
            'roles:id,name',
            'permissions:id,name'
        ])
        ->whereDoesntHave('roles', function($query) {
            $query->where('name', UserRoles::ADMIN->value);
        })
        ->select('id','name','last_name','email','curp', 'phone_number','address','blood_type')
        ->paginate($perPage, ['*'], 'page', $page);

        $paginator->getCollection()->transform(function ($user) {
            $user->roles = $user->roles->pluck('name')->toArray();
            $user->permissions = $user->permissions->pluck('name')->toArray();
            return $user;
        });

        $students = $paginator->getCollection()->filter(function ($user) {
        return in_array(UserRoles::STUDENT->value, $user->roles);
        });

        if ($students->isNotEmpty()) {
            $students->load([
                'studentDetail' => function ($query) {
                    $query->select('id', 'user_id', 'career_id', 'n_control', 'semestre', 'group')
                        ->with(['career:id,career_name']);
                }
            ]);

             $students->transform(function ($user) {
                if ($user->relationLoaded('studentDetail') && $user->studentDetail) {
                    $user->studentDetail->career = optional($user->studentDetail->career)->name;
                    unset($user->studentDetail->career_id);
                }
                return $user;
            });
        }

        return $paginator;
    }
    public function findAuthUser(): ?User
    {
        /** @var \App\Models\User $user */
        $user=Auth::user();
        $isStudent=$this->hasRole($user->id,UserRoles::STUDENT->value);
        if($isStudent){
            $user->load('studentDetail');
        }
        return optional($user, fn($user) => UserMapper::toDomain($user));
    }

    public function findByIds(array $ids): iterable
    {
        if(empty($ids))
        {
            return [];
        }
         return EloquentUser::whereIn('id', $ids)
        ->lazy()
        ->map(fn($user) => UserMapper::toDomain($user));
    }
    public function findModelEntity(int $userId): EloquentUser
    {
        return EloquentUser::findOrFail($userId);
    }

    public function getUsersByRole(string $role): Collection
    {
        return EloquentUser::role($role)->get(['id', 'name', 'last_name', 'curp']);
    }

    public function getUsersByCurp(array $curps): Collection
    {
        return EloquentUser::whereIn('curp', $curps)->get(['id', 'name', 'last_name', 'curp']);
    }
}
