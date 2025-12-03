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
            ->leftJoin('payment_concept_user', 'payment_concepts.id', '=', 'payment_concept_user.payment_concept_id')
            ->leftJoin('career_payment_concept', 'payment_concepts.id', '=', 'career_payment_concept.payment_concept_id')
            ->leftJoin('payment_concept_semester', 'payment_concepts.id', '=', 'payment_concept_semester.payment_concept_id')
            ->leftJoin('payment_concept_applicant_tags', 'payment_concepts.id', '=', 'payment_concept_applicant_tags.payment_concept_id')

            ->join('users', 'users.id', '=', 'payment_concept_user.user_id')
            ->leftJoin('student_details', 'student_details.user_id', '=', 'users.id')

            ->whereIn('users.id', $userIds)

            ->whereDate('payment_concepts.start_date', '<=', $now)
            ->where(function ($q) use ($now) {
                $q->whereNull('payment_concepts.end_date')
                    ->orWhereDate('payment_concepts.end_date', '>=', $now);
            })

            ->where(function ($q) {
                $q->where('payment_concepts.is_global', true)
                    ->orWhereNotNull('payment_concept_user.user_id')
                    ->orWhereNotNull('career_payment_concept.career_id')
                    ->orWhereNotNull('payment_concept_semester.semestre')
                    ->orWhereNotNull('payment_concept_applicant_tags.tag');
            })

            ->whereNotExists(function ($q) {
                $q->select(DB::raw(1))
                    ->from('payments')
                    ->whereColumn('payments.payment_concept_id', 'payment_concepts.id')
                    ->whereColumn('payments.user_id', 'users.id');
            })

            ->whereNotExists(function ($q) {
                $q->select(DB::raw(1))
                    ->from('concept_exceptions')
                    ->whereColumn('concept_exceptions.payment_concept_id', 'payment_concepts.id')
                    ->whereColumn('concept_exceptions.user_id', 'users.id');
            });
    }
}

