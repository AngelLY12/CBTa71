<?php

namespace App\Core\Infraestructure\Repositories\Query\Payments;

use App\Core\Domain\Repositories\Query\Payments\PaymentQueryRepInterface;
use App\Core\Application\Mappers\PaymentMapper as MappersPaymentMapper;
use App\Core\Domain\Entities\Payment;
use App\Core\Domain\Entities\PaymentMethod;
use App\Core\Domain\Entities\User;
use App\Core\Infraestructure\Mappers\PaymentMapper;
use App\Models\Payment as EloquentPayment;
use Generator;
use Illuminate\Pagination\LengthAwarePaginator;

class EloquentPaymentQueryRepository implements PaymentQueryRepInterface
{
    public function sumPaymentsByUserYear(User $user): string {
        $total = EloquentPayment::where('user_id', $user->id)
        ->whereYear('created_at', now()->year)
        ->sum('amount');

        return number_format($total, 2, '.', '');
    }

    public function getConceptNameFromPayment(string $paymentIntentId): ?string
    {
        $conceptName = EloquentPayment::where('payment_intent_id', $paymentIntentId)
        ->value('concept_name');
        return $conceptName;
    }


    public function getPaymentHistory(User $user, int $perPage, int $page): LengthAwarePaginator
    {
         return EloquentPayment::where('user_id', $user->id)
        ->select('id', 'concept_name', 'amount', 'created_at')
        ->orderBy('created_at','desc')
        ->paginate($perPage, ['*'], 'page', $page)
        ->through(fn($p) => MappersPaymentMapper::toHistoryResponse($p));
    }

    public function getPaymentHistoryWithDetails(User $user, int $perPage, int $page): LengthAwarePaginator
    {
        return EloquentPayment::where('user_id', $user->id)
            ->select('id', 'concept_name', 'amount', 'status','payment_intent_id','url','payment_method_details', 'created_at')
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
        return number_format($query->sum('amount'), 2, '.', '');
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
            'paymentConcept:id,concept_name'
        ])
        ->when($search, function ($q) use ($search) {
            $q->whereHas('user', fn($sub) =>
                $sub->where('name', 'like', "%$search%")
                    ->orWhere('last_name', 'like', "%$search%")
                    ->orWhere('email', 'like', "%$search%")
            )->orWhereHas('paymentConcept', fn($sub) =>
                $sub->where('concept_name', 'like', "%$search%")
            );
        })
        ->paginate($perPage, ['*'], 'page', $page)
        ->through(fn($p) => MappersPaymentMapper::toListItemResponse($p));
    }


    public function getPaidWithinLastMonthCursor(): Generator
    {
        foreach (EloquentPayment::where('status', 'paid')
                ->where('created_at', '>=', now()->subMonths(1))
                ->cursor() as $model) {
            yield PaymentMapper::toDomain($model);
        }
    }


    public function updatePaymentWithStripeData(Payment $payment, $pi, $charge, PaymentMethod $savedPaymentMethod): void
    {
        if (!$payment->id) {
            logger()->warning("El pago no tiene ID, no se puede actualizar.");
            return;
        }

        $eloquent= EloquentPayment::findOrFail($payment->id);

        $paymentMethodDetails = $this->formatPaymentMethodDetails($charge->payment_method_details);
        $eloquent->update([
            'payment_method_id' => $savedPaymentMethod?->id,
            'stripe_payment_method_id' => $charge?->payment_method,
            'status' => $pi->status,
            'payment_method_details'=>$paymentMethodDetails,
            'url' => $charge?->receipt_url ?? $payment->url,
        ]);
        logger()->info("Pago {$payment->id} actualizado correctamente.");

    }
    private function formatPaymentMethodDetails($details): array
    {
        if ($details->type === 'card' && isset($details->card)) {
            return [
                'type' => $details->type,
                'brand' => $details->card->brand,
                'last4' => $details->card->last4,
                'funding' => $details->card->funding,
            ];
        }

        return (array) $details;
    }
}
