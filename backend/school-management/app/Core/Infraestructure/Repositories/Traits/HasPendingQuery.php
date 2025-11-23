<?php

namespace App\Core\Infraestructure\Traits;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

trait HasPendingQuery
{
    private function basePendingQuery(array $userIds)
    {
        $now = Carbon::now()->toDateString();

        return DB::table('payment_concepts')
            ->join('payment_concept_user', 'payment_concepts.id', '=', 'payment_concept_user.payment_concept_id')
            ->join('users', 'users.id', '=', 'payment_concept_user.user_id')
            ->whereIn('users.id', $userIds)
            ->whereDate('payment_concepts.start_date', '<=', $now)
            ->where(function ($q) use ($now) {
                $q->whereNull('payment_concepts.end_date')
                ->orWhereDate('payment_concepts.end_date', '>=', $now);
            })
            ->whereNotExists(function ($q) {
                $q->select(DB::raw(1))
                ->from('payments')
                ->whereColumn('payments.payment_concept_id', 'payment_concepts.id')
                ->whereColumn('payments.user_id', 'users.id');
            });
    }

}
