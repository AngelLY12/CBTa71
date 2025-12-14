<?php

namespace App\Core\Infraestructure\Traits;
use App\Core\Domain\Enum\PaymentConcept\PaymentConceptApplicantType;
use App\Core\Domain\Enum\PaymentConcept\PaymentConceptStatus;
use App\Core\Domain\Enum\User\UserRoles;
use App\Core\Domain\Enum\User\UserStatus;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

trait HasPendingQuery
{
    public function basePendingQuery(array $userIds)
    {
        $now = Carbon::now()->toDateString();

        $usersContext = DB::table('users')
            ->leftJoin('student_details', 'student_details.user_id', '=', 'users.id')
            ->whereIn('users.id', $userIds)
            ->where('users.status', UserStatus::ACTIVO->value)
            ->select(
                'users.id as user_id',
                'student_details.career_id',
                'student_details.semestre',
                DB::raw("
                    CASE
                        WHEN student_details.id IS NULL
                            THEN '" . PaymentConceptApplicantType::NO_STUDENT_DETAILS->value . "'
                        ELSE '" . PaymentConceptApplicantType::APPLICANT->value . "'
                    END as applicant_type
                ")
            );

        $baseConcepts = DB::table('payment_concepts')
            ->where('payment_concepts.status', PaymentConceptStatus::ACTIVO->value)
            ->whereDate('payment_concepts.start_date', '<=', $now)
            ->where(function ($q) use ($now) {
                $q->whereNull('payment_concepts.end_date')
                    ->orWhereDate('payment_concepts.end_date', '>=', $now);
            });

        $pending = DB::query()
            ->fromSub($usersContext, 'u')
            ->joinSub($baseConcepts, 'payment_concepts', fn () => true)

            ->leftJoin('payments', function ($q) {
                $q->on('payments.payment_concept_id', '=', 'payment_concepts.id')
                    ->on('payments.user_id', '=', 'u.user_id');
            })

            ->leftJoin('concept_exceptions', function ($q) {
                $q->on('concept_exceptions.payment_concept_id', '=', 'payment_concepts.id')
                    ->on('concept_exceptions.user_id', '=', 'u.user_id');
            })

            ->whereNull('payments.id')
            ->whereNull('concept_exceptions.id')

            ->where(function ($q) {

                $q->where(function ($q) {
                    $q->where('payment_concepts.is_global', true)
                        ->whereExists(function ($r) {
                            $r->select(DB::raw(1))
                                ->from('role_user')
                                ->join('roles', 'roles.id', '=', 'role_user.role_id')
                                ->whereColumn('role_user.user_id', 'u.user_id')
                                ->where('roles.name', UserRoles::STUDENT->value);
                        });
                });

                $q->orWhereExists(function ($sub) {
                    $sub->select(DB::raw(1))
                        ->from('payment_concept_user')
                        ->whereColumn('payment_concept_user.payment_concept_id', 'payment_concepts.id')
                        ->whereColumn('payment_concept_user.user_id', 'u.user_id');
                });

                $q->orWhereExists(function ($sub) {
                    $sub->select(DB::raw(1))
                        ->from('payment_concept_career')
                        ->whereColumn('payment_concept_career.payment_concept_id', 'payment_concepts.id')
                        ->whereColumn('payment_concept_career.career_id', 'u.career_id');
                });

                $q->orWhereExists(function ($sub) {
                    $sub->select(DB::raw(1))
                        ->from('payment_concept_semesters')
                        ->whereColumn('payment_concept_semesters.payment_concept_id', 'payment_concepts.id')
                        ->whereColumn('payment_concept_semesters.semestre', 'u.semestre');
                });

                $q->orWhereExists(function ($sub) {
                    $sub->select(DB::raw(1))
                        ->from('payment_concept_applicant_tags')
                        ->whereColumn('payment_concept_applicant_tags.payment_concept_id', 'payment_concepts.id')
                        ->whereColumn('payment_concept_applicant_tags.tag', 'u.applicant_type');
                });
            })

            ->select(
                'payment_concepts.*',
                'u.user_id as target_user_id'
            );

        return $pending;
    }
}


