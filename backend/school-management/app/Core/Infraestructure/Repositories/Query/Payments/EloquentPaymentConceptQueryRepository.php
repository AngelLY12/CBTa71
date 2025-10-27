<?php

namespace App\Core\Infraestructure\Repositories\Query\Payments;

use App\Core\Application\DTO\Response\PaymentConcept\PendingSummaryResponse;
use App\Core\Application\Mappers\PaymentConceptMapper as MappersPaymentConceptMapper;
use App\Core\Application\Mappers\UserMapper;
use App\Core\Domain\Repositories\Query\Payments\PaymentConceptQueryRepInterface;
use App\Core\Domain\Entities\User;
use App\Core\Infraestructure\Mappers\PaymentConceptMapper;
use App\Models\PaymentConcept as EloquentPaymentConcept;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

class EloquentPaymentConceptQueryRepository implements PaymentConceptQueryRepInterface
{
    public function getPendingPaymentConcepts(User $user): PendingSummaryResponse {
        $query = $this->basePendingPaymentConcept($user);
        $result = $query->selectRaw('SUM(amount) as total_amount, COUNT(id) as total_count')->first();
        return MappersPaymentConceptMapper::toPendingPaymentSummary($result->toArray());
    }

    public function getStudentsWithPendingSummary(array $userIds): array
    {
        $concepts = $this->basePendingPaymentConcept()
            ->whereHas('users', fn($q) => $q->whereIn('users.id', $userIds))
            ->with([
                'users:id,name,last_name',
                'users.studentDetail:user_id,career_id,semestre',
                'users.studentDetail.career:id,career_name'
            ])
            ->get(['id', 'concept_name', 'amount']);

        return $concepts
            ->flatMap(function ($concept) {
                return $concept->users->map(fn($u) => [
                    'user_id' => $u->id,
                    'name' => "{$u->name} {$u->last_name}",
                    'amount' => $concept->amount,
                    'semestre' => $u->studentDetail?->semestre,
                    'career' => $u->studentDetail?->career?->career_name,
                ]);
            })
            ->groupBy('user_id')
            ->map(fn($group, $userId) =>
                UserMapper::toUserWithPendingSummaryResponse(
                [
                    'user_id' => $userId,
                    'name' => $group->first()['name'],
                    'semestre' => $group->first()['semestre'],
                    'career' => $group->first()['career'] ?? null,
                    'total_count' => $group->count(),
                    'total_amount' => $group->sum(fn($item) => $item['amount']),
                ]))
            ->values()
            ->toArray();
    }

    public function getPendingPaymentConceptsWithDetails(User $user): array
    {
        return $this->basePendingPaymentConcept($user)
                ->select('id','concept_name','description','amount','start_date','end_date')
                ->orderBy('created_at','desc')
                ->get()
                ->map(fn($pc)=> MappersPaymentConceptMapper::toPendingPaymentConceptResponse($pc->toArray()))
                ->toArray();
    }

    public function countOverduePayments(User $user): int {
        return $this->baseOverduePaymentConcept($user)->count();
    }

    public function getOverduePayments(User $user): array
    {
        return $this->baseOverduePaymentConcept($user)
        ->select('id','concept_name','description','amount','start_date','end_date')
            ->get()
            ->map(fn($pc)=> MappersPaymentConceptMapper::toPendingPaymentConceptResponse($pc->toArray()))
            ->toArray();
    }

    public function findAllConcepts(string $status = 'todos'): array
    {
        $query = EloquentPaymentConcept::query()
            ->select('id','concept_name','description','status','start_date','end_date','amount','applies_to','is_global')
            ->orderBy('created_at','desc');

        if($status !== 'todos') {
            $query->where('status', $status);
        }

        return $query->get()->map(fn($pc) => PaymentConceptMapper::toDomain($pc))->toArray();
    }

    public function getAllPendingPaymentAmount(bool $onlyThisYear = false): PendingSummaryResponse
    {
        $query = $this->basePendingPaymentConcept();

        if ($onlyThisYear) {
            $query->whereYear('created_at', now()->year);
        }

        $result = $query->selectRaw('SUM(amount) as total_amount, COUNT(id) as total_count')->first();

        return MappersPaymentConceptMapper::toPendingPaymentSummary($result->toArray());
    }

    public function getConceptsToDashboard(bool $onlyThisYear = false): array
    {
        $query = EloquentPaymentConcept::select(
        'id', 'concept_name', 'status', 'start_date', 'end_date', 'amount','applies_to'
        )->orderBy('created_at', 'desc');

        if ($onlyThisYear) {
            $query->whereYear('created_at', now()->year);
        }

        return $query->get()
        ->map(fn($pc) => MappersPaymentConceptMapper::toConceptsToDashboardResponse($pc))
        ->toArray();
    }

    public function getPendingWithDetailsForStudents(array $userIds): array
    {
        return $this->basePendingPaymentConcept()
        ->whereHas('users', fn($q) => $q->whereIn('users.id', $userIds))
        ->with('users:id,name,last_name')
        ->select('id', 'concept_name', 'amount')
        ->get()
        ->flatMap(function ($concept) {
        return $concept->users->map(fn($u) => MappersPaymentConceptMapper::toConceptNameAndAmoutResonse([
                'user_name' => $u->name .' '. $u->last_name,
                'concept_name' => $concept->concept_name,
                'amount' => $concept->amount,
            ]));
        })
        ->values()
        ->toArray();
    }

    public function finalizePaymentConcepts(): void
    {
        $today = Carbon::today();

        EloquentPaymentConcept::where('status', 'activo')
        ->whereDate('end_date', '<', $today)
        ->update(['status' => 'finalizado']);

    }


    private function basePendingPaymentConcept(?User $user = null): Builder
    {
        $query = EloquentPaymentConcept::query()
         ->where(function($q) {
          $q->whereDate('start_date', '<=', now())
            ->where(function($q2) {
                $q2->whereNull('end_date')
                   ->orWhereDate('end_date', '>=', now());
            });
        });

        if ($user) {
            $query->whereDoesntHave('payments', fn($q) => $q->where('user_id', $user->id));

            $query->where(function ($q) use ($user) {
                $q->where('is_global', true)
                    ->orWhereHas('users', fn($q) => $q->where('users.id', $user->id))
                    ->orWhereHas('careers', function ($q) use ($user) {
                        if ($user->studentDetail?->career_id) {
                            $q->where('careers.id', $user->studentDetail->career_id);
                        }
                    })
                    ->orWhereHas('paymentConceptSemesters', function ($q) use ($user) {
                        if ($user->studentDetail?->semestre) {
                            $q->where('semestre', $user->studentDetail->semestre);
                        }
                    });
            });
        } else {
            $query->whereDoesntHave('payments')
                ->where(function($q){
                    $q->where('is_global', true)
                        ->orWhereHas('users', fn($q) => $q->role('student')->where('status','activo'))
                        ->orWhereHas('careers', fn($q) => $q->whereHas('students', fn($q2) => $q2->role('student')->where('status','activo')));
            });
        }
        return $query;
    }

    private function baseOverduePaymentConcept(User $user): Builder
    {
        $studentDetail = $user->studentDetail;
        $careerId = $studentDetail?->career_id;
        $semester = $studentDetail?->semestre;

        $query = EloquentPaymentConcept::query()
            ->where('status', 'finalizado')
            ->whereDoesntHave('payments', fn($q) => $q->where('user_id', $user->id))
            ->where(function ($q) use ($user, $careerId, $semester) {
                $q->where('is_global', true)
                ->orWhereHas('users', fn($q) => $q->where('users.id', $user->id))
                ->orWhereHas('careers', fn($q) => $careerId ? $q->where('careers.id', $careerId) : $q)
                ->orWhereHas('paymentConceptSemesters', fn($q) => $semester ? $q->where('semestre', $semester) : $q);
            });

        return $query;
    }
}
