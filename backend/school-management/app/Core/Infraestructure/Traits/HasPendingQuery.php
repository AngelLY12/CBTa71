<?php

namespace App\Core\Infraestructure\Traits;
use App\Core\Domain\Enum\User\UserRoles;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

trait HasPendingQuery
{
    public function basePendingQuery(array $userIds)
    {
        $now = Carbon::now()->toDateString();

        $individual = DB::table('payment_concepts')
            ->join('payment_concept_user', 'payment_concepts.id', '=', 'payment_concept_user.payment_concept_id')
            ->whereIn('payment_concept_user.user_id', $userIds)
            ->whereDate('payment_concepts.start_date', '<=', $now)
            ->where(function ($q) use ($now) {
                $q->whereNull('payment_concepts.end_date')
                    ->orWhereDate('payment_concepts.end_date', '>=', $now);
            })
            ->whereNotExists(function ($q) {
                $q->select(DB::raw(1))
                    ->from('payments')
                    ->whereColumn('payments.payment_concept_id', 'payment_concepts.id')
                    ->whereColumn('payments.user_id', 'payment_concept_user.user_id');
            })
            ->whereNotExists(function ($q) {
                $q->select(DB::raw(1))
                    ->from('concept_exceptions')
                    ->whereColumn('concept_exceptions.payment_concept_id', 'payment_concepts.id')
                    ->whereColumn('concept_exceptions.user_id', 'payment_concept_user.user_id');
            })
            ->select([
                'payment_concepts.*',
                'payment_concept_user.user_id as target_user_id'
            ]);


        $global = DB::table('payment_concepts')
            ->crossJoin(DB::raw('(' . implode(',', $userIds) . ') as u(user_id)'))
            ->where('payment_concepts.is_global', true)
            ->join('role_user', 'role_user.user_id', '=', 'u.user_id')
            ->join('roles', 'roles.id', '=', 'role_user.role_id')
            ->where('roles.name', UserRoles::STUDENT->value)
            ->whereDate('payment_concepts.start_date', '<=', $now)
            ->where(function ($q) use ($now) {
                $q->whereNull('payment_concepts.end_date')
                    ->orWhereDate('payment_concepts.end_date', '>=', $now);
            })
            ->whereNotExists(function ($q) {
                $q->select(DB::raw(1))
                    ->from('payments')
                    ->whereColumn('payments.payment_concept_id', 'payment_concepts.id')
                    ->whereColumn('payments.user_id', 'u.user_id');
            })
            ->whereNotExists(function ($q) {
                $q->select(DB::raw(1))
                    ->from('concept_exceptions')
                    ->whereColumn('concept_exceptions.payment_concept_id', 'payment_concepts.id')
                    ->whereColumn('concept_exceptions.user_id', 'u.user_id');
            })
            ->select([
                'payment_concepts.*',
                DB::raw('u.user_id as target_user_id')
            ]);


        return DB::query()->fromSub(
            $individual->unionAll($global),
            'pending_concepts'
        );
    }
}

