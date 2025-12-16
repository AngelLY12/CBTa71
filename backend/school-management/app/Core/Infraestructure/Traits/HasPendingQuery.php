<?php

namespace App\Core\Infraestructure\Traits;
use App\Core\Domain\Enum\Payment\PaymentStatus;
use App\Core\Domain\Enum\PaymentConcept\PaymentConceptApplicantType;
use App\Core\Domain\Enum\PaymentConcept\PaymentConceptStatus;
use App\Core\Domain\Enum\PaymentConcept\PaymentConceptTimeScope;
use App\Core\Domain\Enum\User\UserRoles;
use App\Core\Domain\Enum\User\UserStatus;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

trait HasPendingQuery
{
    public function basePendingQuery(array $userIds, PaymentConceptTimeScope $scope= PaymentConceptTimeScope::ONLY_ACTIVE)
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
            ->when(
                $scope === PaymentConceptTimeScope::ONLY_ACTIVE,
                fn($q) =>
                $q->where(function ($q) use ($now) {
                    $q->whereNull('payment_concepts.end_date')
                        ->orWhereDate('payment_concepts.end_date', '>=', $now);
                })

            );


        $pending = DB::query()
            ->fromSub($usersContext, 'u')
            ->joinSub($baseConcepts, 'payment_concepts', fn () => true)

            ->leftJoin('payments', function ($q) {
                $q->on('payments.payment_concept_id', '=', 'payment_concepts.id')
                    ->on('payments.user_id', '=', 'u.user_id')
                    ->whereNotIn('payments.status', PaymentStatus::terminalStatuses());;
            })

            ->leftJoin('concept_exceptions', function ($q) {
                $q->on('concept_exceptions.payment_concept_id', '=', 'payment_concepts.id')
                    ->on('concept_exceptions.user_id', '=', 'u.user_id');
            })

            ->where(function($q) {
                $q->whereNull('payments.id')
                    ->orWhere(function($q2) {
                        $q2->whereIn('payments.status', [PaymentStatus::UNDERPAID->value]);
                        $q2->whereRaw('payments.id = (
                          SELECT MAX(p2.id)
                          FROM payments p2
                          WHERE p2.payment_concept_id = payments.payment_concept_id
                            AND p2.user_id = payments.user_id
                      )');
                    });
            })
            ->whereNull('concept_exceptions.id')

            ->where(function ($q) {

                $q->where(function ($q) {
                    $q->where('payment_concepts.is_global', true)
                        ->whereExists(function ($r) {
                            $r->select(DB::raw(1))
                                ->from('model_has_roles')
                                ->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
                                ->whereColumn('model_has_roles.model_id', 'u.user_id')
                                ->where('model_has_roles.model_type', User::class)
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
                        ->from('career_payment_concept')
                        ->whereColumn('career_payment_concept.payment_concept_id', 'payment_concepts.id')
                        ->whereColumn('career_payment_concept.career_id', 'u.career_id');
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
                DB::raw('COALESCE(payment_concepts.amount - COALESCE(p.amount_received, 0), payment_concepts.amount) as amount'),
                'u.user_id as target_user_id',
                DB::raw("
                    CASE
                        WHEN payment_concepts.end_date IS NOT NULL
                             AND payment_concepts.end_date < CURRENT_DATE
                        THEN 1
                        ELSE 0
                    END as is_expired
                ")
            );

        return $pending;
    }

    public function basePendingLeftJoinQuery(array $userIds, PaymentConceptTimeScope $scope = PaymentConceptTimeScope::INCLUDE_EXPIRED)
    {
        return DB::table('users')
            ->whereIn('users.id', $userIds)
            ->leftJoinSub(
                $this->basePendingQuery($userIds, $scope),
                'pending_concepts',
                'pending_concepts.target_user_id',
                '=',
                'users.id'
            );
    }

}


