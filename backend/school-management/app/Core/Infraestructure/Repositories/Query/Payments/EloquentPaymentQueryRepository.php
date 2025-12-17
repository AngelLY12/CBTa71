<?php

namespace App\Core\Infraestructure\Repositories\Query\Payments;

use App\Core\Domain\Repositories\Query\Payments\PaymentQueryRepInterface;
use App\Core\Application\Mappers\PaymentMapper as MappersPaymentMapper;
use App\Core\Domain\Entities\Payment;
use App\Core\Domain\Enum\Payment\PaymentStatus;
use App\Core\Infraestructure\Mappers\PaymentMapper;
use App\Models\Payment as EloquentPayment;
use Generator;
use Illuminate\Pagination\LengthAwarePaginator;

class EloquentPaymentQueryRepository implements PaymentQueryRepInterface
{

    public function findById(int $id): ?Payment
    {
        return optional(EloquentPayment::find($id), fn($pc) => PaymentMapper::toDomain($pc));
    }

    public function findBySessionId(string $sessionId): ?Payment
    {
        $payment= EloquentPayment::where('stripe_session_id', $sessionId)->first();
        return $payment ? PaymentMapper::toDomain($payment) : null;
    }

    public function findByIntentId(string $intentId): ?Payment
    {
        $payment=EloquentPayment::where('payment_intent_id', $intentId)->first();
        return $payment ? PaymentMapper::toDomain($payment) : null;
    }

    public function sumPaymentsByUserYear(int $userId, bool $onlyThisYear): string {
        $query = EloquentPayment::where('user_id', $userId);
        if ($onlyThisYear) {
            $query->whereYear('created_at', now()->year);
        }
        $total=$query->sum('amount_received');

        return number_format($total, 2, '.', '');
    }

    public function getPaymentHistory(int $userId, int $perPage, int $page, bool $onlyThisYear): LengthAwarePaginator
    {
         $query= EloquentPayment::where('user_id', $userId)
        ->select('id', 'concept_name', 'amount', 'amount_received' ,'created_at');
        if ($onlyThisYear) {
            $query->whereYear('created_at', now()->year);
        }
        return $query->orderBy('created_at','desc')
        ->paginate($perPage, ['*'], 'page', $page)
        ->through(fn($p) => MappersPaymentMapper::toHistoryResponse($p));
    }

    public function getPaymentHistoryWithDetails(int $userId, int $perPage, int $page): LengthAwarePaginator
    {
        return EloquentPayment::where('user_id', $userId)
            ->select('id', 'concept_name', 'amount', 'amount_received','status','payment_intent_id','url','payment_method_details', 'created_at')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage, ['*'], 'page', $page)
            ->through(fn($p) => MappersPaymentMapper::toDetailResponse($p));
    }


    public function getAllPaymentsMade(bool $onlyThisYear): string
    {
       $query = EloquentPayment::query();
        if ($onlyThisYear) {
            $query->whereYear('created_at', now()->year);
        }
        return number_format($query->sum('amount_received'), 2, '.', '');
    }

    public function findByIntentOrSession(int $userId, string $paymentIntentId): ?Payment
    {
        $payment=EloquentPayment::
            where('user_id', $userId)
            ->where(function ($q) use ($paymentIntentId) {
                $q->where('payment_intent_id', $paymentIntentId)
                  ->orWhere('stripe_session_id', $paymentIntentId);
            })
            ->first();
        return $payment ? PaymentMapper::toDomain($payment):null;
    }

    public function getAllWithSearchEager(?string $search, int $perPage, int $page): LengthAwarePaginator
    {
        return EloquentPayment::with([
            'user:id,name,last_name',
        ])
        ->when($search, function ($q) use ($search) {
            $q->whereHas('user', fn($sub) =>
                $sub->where('name', 'like', "%$search%")
                    ->orWhere('last_name', 'like', "%$search%")
                    ->orWhere('email', 'like', "%$search%")
            )->orWhere('concept_name', 'like', "%$search%");

        })
        ->paginate($perPage, ['*'], 'page', $page)
        ->through(fn($p) => MappersPaymentMapper::toListItemResponse($p));
    }


     /**
     * @return Generator<int, Payment>
     */
    public function getPaidWithinLastMonthCursor(): Generator
    {
        foreach (EloquentPayment::whereIn('status', PaymentStatus::reconcilableStatuses())
                     ->whereBetween('created_at', [
                         now()->subMonth(),
                         now()->subMinutes(10),
                     ])
                ->cursor() as $model) {
            yield PaymentMapper::toDomain($model);
        }
    }

    public function getLastPaymentForConcept(int $userId, int $conceptId, array $allowedStatuses = []): ?Payment
    {
        $query = EloquentPayment::query()
            ->where('user_id', $userId)
            ->where('payment_concept_id', $conceptId);

        if (!empty($allowedStatuses)) {
            $query->whereIn('status', $allowedStatuses);
        }

        return PaymentMapper::toDomain($query->orderByDesc('id')->first());
    }
}
