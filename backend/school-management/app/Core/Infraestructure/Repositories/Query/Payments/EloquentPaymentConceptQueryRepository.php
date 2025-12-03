<?php

namespace App\Core\Infraestructure\Repositories\Query\Payments;

use App\Core\Application\DTO\Response\PaymentConcept\PendingSummaryResponse;
use App\Core\Application\Mappers\PaymentConceptMapper as MappersPaymentConceptMapper;
use App\Core\Domain\Entities\PaymentConcept;
use App\Core\Domain\Enum\PaymentConcept\PaymentConceptApplicantType;
use App\Core\Domain\Enum\User\UserRoles;
use App\Core\Domain\Repositories\Query\Payments\PaymentConceptQueryRepInterface;
use App\Core\Domain\Entities\User;
use App\Core\Domain\Enum\PaymentConcept\PaymentConceptStatus;
use App\Core\Infraestructure\Mappers\PaymentConceptMapper;
use App\Core\Infraestructure\Traits\HasPendingQuery;
use App\Models\PaymentConcept as EloquentPaymentConcept;
use Carbon\Carbon;
use Google\Service\Dfareporting\UserRole;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class EloquentPaymentConceptQueryRepository implements PaymentConceptQueryRepInterface
{
    use HasPendingQuery;

    public function findById(int $id): ?PaymentConcept
    {
        return optional(EloquentPaymentConcept::find($id), fn($pc) => PaymentConceptMapper::toDomain($pc));
    }
    public function getPendingPaymentConcepts(User $user): PendingSummaryResponse {
        $result = $this->basePaymentConcept($user)
            ->selectRaw('COALESCE(SUM(amount), 0) as total_amount, COUNT(id) as total_count')
            ->first();

        return MappersPaymentConceptMapper::toPendingPaymentSummary((array)$result);
    }

    public function getPendingPaymentConceptsWithDetails(User $user): array
    {
        $rows = $this->basePaymentConcept($user)
            ->addSelect(['payment_concepts.concept_name', 'payment_concepts.description', 'payment_concepts.amount', 'payment_concepts.start_date', 'payment_concepts.end_date'])
            ->orderBy('payment_concepts.created_at', 'desc')
            ->get();

        return $rows->map(fn($pc) => MappersPaymentConceptMapper::toPendingPaymentConceptResponse($pc->toArray()))->toArray();
    }

    public function countOverduePayments(User $user): int
    {
        return $this->basePaymentConcept($user, onlyActive: false, status: PaymentConceptStatus::FINALIZADO)->count();
    }

    public function getOverduePayments(User $user): array
    {
        $rows = $this->basePaymentConcept($user, onlyActive: false, status: PaymentConceptStatus::FINALIZADO)
            ->addSelect(['payment_concepts.concept_name', 'payment_concepts.description', 'payment_concepts.amount', 'payment_concepts.start_date', 'payment_concepts.end_date'])
            ->get();

        return $rows->map(fn($pc) => MappersPaymentConceptMapper::toPendingPaymentConceptResponse($pc->toArray()))->toArray();
    }

    public function findAllConcepts(string $status, int $perPage, int $page): LengthAwarePaginator
    {
        $query = EloquentPaymentConcept::query()
            ->select(['id','concept_name','description','status','start_date','end_date','amount','applies_to','is_global'])
            ->latest('created_at');

        if ($status !== 'todos') {
            $query->where('status', $status);
        }

        return $query->paginate($perPage, ['*'], 'page', $page);
    }

    public function getAllPendingPaymentAmount(bool $onlyThisYear): PendingSummaryResponse
    {
        $query = $this->basePaymentConcept(null, onlyActive: true);

        if ($onlyThisYear) {
            $query->whereYear('payment_concepts.created_at', now()->year);
        }

        $result = $query->selectRaw('COALESCE(SUM(payment_concepts.amount), 0) as total_amount, COUNT(payment_concepts.id) as total_count')->first();

        return MappersPaymentConceptMapper::toPendingPaymentSummary($result->toArray());
    }

    public function getConceptsToDashboard(bool $onlyThisYear, int $perPage, int $page): LengthAwarePaginator
    {
        $query = EloquentPaymentConcept::select(['id', 'concept_name', 'status', 'start_date', 'end_date', 'amount', 'applies_to'])
                ->latest('created_at');

            if ($onlyThisYear) {
                $query->whereYear('created_at', now()->year);
            }

         $paginator = $query->paginate($perPage);

        $paginator->getCollection()->transform(fn($pc) => MappersPaymentConceptMapper::toConceptsToDashboardResponse($pc));

        return $paginator;
    }

    public function getPendingWithDetailsForStudents(array $userIds): array
    {
        if (empty($userIds)) {
            return [];
        }
        $now = Carbon::now()->toDateString();
        $rows = $this->basePendingQuery($userIds)
            ->select('users.id as user_id', DB::raw("CONCAT(users.name, ' ', users.last_name) as user_name"), 'payment_concepts.concept_name', 'payment_concepts.amount')
            ->get();
        return $rows->map(fn($r) => MappersPaymentConceptMapper::toConceptNameAndAmoutResonse([
            'user_name' => $r->user_name,
            'concept_name' => $r->concept_name,
            'amount' => $r->amount,
        ]))->toArray();

    }

    public function finalizePaymentConcepts(): void
    {
        $today = Carbon::today();

        EloquentPaymentConcept::where('status', PaymentConceptStatus::ACTIVO)
        ->whereDate('end_date', '<', $today)
        ->update(['status' => PaymentConceptStatus::FINALIZADO]);

    }

     private function basePaymentConcept(?User $user = null, $onlyActive=true, ?PaymentConceptStatus $status=null): Builder
    {
        $now = now();

        $query = EloquentPaymentConcept::query()
            ->select(['payment_concepts.id']);
        if ($onlyActive) {
            $now = now();
            $query->whereDate('start_date', '<=', $now)
                ->where(fn($q) => $q->whereNull('end_date')->orWhereDate('end_date', '>=', $now));
        }

        if ($status) {
            $query->where('status', $status);
        }

        if ($user) {
            $userId = $user->id;
            $careerId = $user->studentDetail?->career_id;
            $semester = $user->studentDetail?->semestre;
            $isApplicant = $user->isApplicant();
            $isNewStudent = $user->isNewStudent();

            $query->whereNotExists(function ($sub) use ($userId) {
                $sub->select(DB::raw(1))
                    ->from('payments')
                    ->whereColumn('payments.payment_concept_id', 'payment_concepts.id')
                    ->where('payments.user_id', $userId);
            });
            $query->whereDoesntHave('exceptions', fn($q) =>
                $q->where('user_id', $userId)
            );

            $query->where(function ($q) use ($userId, $careerId, $semester, $isApplicant, $isNewStudent) {
                $q->where('is_global', true)
                ->orWhereHas('users', fn($q) => $q->where('users.id', $userId))
                ->when($careerId, fn($q) =>
                    $q->orWhereHas('careers', fn($q) => $q->where('careers.id', $careerId))
                )
                ->when($semester, fn($q) =>
                    $q->orWhereHas('paymentConceptSemesters', fn($q) => $q->where('semestre', $semester))
                )
                ->when($isApplicant, fn($q) =>
                    $q->orWhereHas('applicantTypes', fn($q) => $q->where('tag', PaymentConceptApplicantType::APPLICANT->value))
                )
                ->when($isNewStudent, fn($q) =>
                    $q->orWhereHas('applicantTypes', fn($q) => $q->where('tag', PaymentConceptApplicantType::NO_STUDENT_DETAILS->value))
                );
            });

        } else {
            $query->whereNotExists(function ($sub) {
                $sub->select(DB::raw(1))
                    ->from('payments')
                    ->whereColumn('payments.payment_concept_id', 'payment_concepts.id');
            })
            ->where(function ($q) {
                $q->where('is_global', true)
                ->orWhereHas('users', fn($q) => $q->role('student')->where('status', 'activo'))
                ->orWhereHas('careers.students', fn($q) => $q->role('student')->where('status', 'activo'));
            });
        }

        return $query;
    }
}
