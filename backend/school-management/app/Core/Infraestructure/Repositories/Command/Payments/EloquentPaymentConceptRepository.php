<?php
namespace App\Core\Infraestructure\Repositories\Command\Payments;

use App\Core\Application\DTO\Response\User\UserIdListDTO;
use App\Core\Domain\Entities\PaymentConcept;
use App\Core\Domain\Enum\PaymentConcept\PaymentConceptStatus;
use App\Core\Domain\Repositories\Command\Payments\PaymentConceptRepInterface;
use App\Core\Infraestructure\Mappers\PaymentConceptMapper;
use App\Models\PaymentConcept as EloquentPaymentConcept;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class EloquentPaymentConceptRepository implements PaymentConceptRepInterface {


    public function create(PaymentConcept $concept): PaymentConcept
    {
        $pc = EloquentPaymentConcept::create(
        PaymentConceptMapper::toPersistence($concept)
        );
        $pc->refresh();
        $pc->load(['careers', 'users', 'paymentConceptSemesters', 'exceptions', 'applicantTypes']);
        return PaymentConceptMapper::toDomain($pc);
    }

    public function update(int $conceptId, array $data): PaymentConcept
    {
        $pc = $this->findOrFail($conceptId);
        $pc->update($data);
        $pc->load(['careers', 'users', 'paymentConceptSemesters', 'exceptions', 'applicantTypes']);
        return PaymentConceptMapper::toDomain($pc);
    }

    public function finalize(PaymentConcept $concept): PaymentConcept
    {
        return $this->update($concept->id, [
            'end_date' => now(),
            'status'   => PaymentConceptStatus::FINALIZADO,
        ]);
    }

    public function activate(PaymentConcept $concept): PaymentConcept
    {
        return $this->update($concept->id,[
            'status'   => PaymentConceptStatus::ACTIVO,
            'end_date' => null,
        ]);
    }

    public function disable(PaymentConcept $concept): PaymentConcept
    {
        return $this->update($concept->id, ['status' => PaymentConceptStatus::DESACTIVADO]);
    }

    public function deleteLogical(PaymentConcept $concept): PaymentConcept
    {
        return $this->update($concept->id, ['status' => PaymentConceptStatus::ELIMINADO]);
    }

    public function delete(int $conceptId): void
    {
        $pc = $this->findOrFail($conceptId);
        $pc->delete();
    }

    public function attachToUsers(int $conceptId, UserIdListDTO $userIds, bool $replaceRelations=false): PaymentConcept
    {
        $pc = $this->findOrFail($conceptId);

        if($replaceRelations){
            $pc->users()->sync($userIds->userIds);
        }else{
            $pc->users()->syncWithoutDetaching($userIds->userIds);
        }

        $pc->refresh();
        return PaymentConceptMapper::toDomain($pc);

    }

    public function attachToCareer(int $conceptId, array $careerIds, bool $replaceRelations=false): PaymentConcept
    {
        $pc = $this->findOrFail($conceptId);
        if($replaceRelations){
            $pc->careers()->sync($careerIds);
        }else{
            $pc->careers()->syncWithoutDetaching($careerIds);
        }
        $pc->refresh();
        return PaymentConceptMapper::toDomain($pc);

    }


    public function attachToSemester(int $conceptId, array $semesters, bool $replaceRelations=false): PaymentConcept
    {
        $pc = $this->findOrFail($conceptId);
        if ($replaceRelations) {
            $pc->paymentConceptSemesters()->where('payment_concept_id', $pc->id)->delete();
        }
        $data = array_map(fn($semester) => [
            'payment_concept_id' => $pc->id,
            'semestre' => $semester,
            'created_at' => now(),
            'updated_at' => now(),
        ], $semesters);

        $pc->paymentConceptSemesters()->upsert(
            $data,
            ['payment_concept_id', 'semestre'],
            ['semestre']
        );
        $pc->refresh();
        return PaymentConceptMapper::toDomain($pc);
    }

    public function attachToExceptionStudents(int $conceptId, UserIdListDTO $userIds, bool $replaceRelations = false): PaymentConcept
    {
        $pc= $this->findOrFail($conceptId);
        if($replaceRelations)
        {
            $pc->exceptions()->sync($userIds->userIds);
        }else{
            $pc->exceptions()->syncWithoutDetaching($userIds->userIds);
        }

        $pc->refresh();
        return PaymentConceptMapper::toDomain($pc);

    }

    public function attachToApplicantTag(int $conceptId, array $tags, bool $replaceRelations = false): PaymentConcept
    {
        $pc = $this->findOrFail($conceptId);
        if($replaceRelations)
        {
            $pc->applicantTypes()->delete();
        }

        $data = array_map(fn($tag) => [
            'payment_concept_id' => $pc->id,
            'tag' => $tag,
            'created_at' => now(),
            'updated_at' => now(),
        ], $tags);

        $pc->applicantTypes()->upsert(
            $data,
            ['payment_concept_id', 'tag'],
            ['tag']
        );
        $pc->refresh();
        return PaymentConceptMapper::toDomain($pc);

    }

    public function detachFromExceptionStudents(int $conceptId): void
    {
        $this->findOrFail($conceptId)->exceptions()->detach();
    }

    public function detachFromSemester(int $conceptId): void
    {
        $this->findOrFail($conceptId)->paymentConceptSemesters()->delete();
    }

    public function detachFromCareer(int $conceptId): void
    {
        $this->findOrFail($conceptId)->careers()->detach();
    }

    public function detachFromUsers(int $conceptId): void
    {
        $this->findOrFail($conceptId)->users()->detach();
    }

    public function detachFromApplicantTag(int $conceptId): void
    {
        $this->findOrFail($conceptId)->applicantTypes()->delete();
    }

    private function findOrFail(int $id): EloquentPaymentConcept
    {
        return EloquentPaymentConcept::with(['careers', 'users', 'paymentConceptSemesters', 'exceptions', 'applicantTypes'])
        ->findOrFail($id);
    }

    public function cleanDeletedConcepts(): int
    {
        $thresholdDate = Carbon::now()->subDays(30);
        return DB::table('payment_concepts')
            ->where('status', PaymentConceptStatus::ELIMINADO)
            ->where('updated_at', '<', $thresholdDate)
            ->delete();
    }

    public function finalizePaymentConcepts(): array
    {
        $today = Carbon::today();

        $updatedConcepts = [];

        EloquentPaymentConcept::where('status', PaymentConceptStatus::ACTIVO)
            ->whereDate('end_date', '<', $today)
            ->chunk(100, function ($concepts) use (&$updatedConcepts) {
                foreach ($concepts as $concept) {
                    $concept->update(['status' => PaymentConceptStatus::FINALIZADO]);

                    $updatedConcepts[] = [
                        'id' => $concept->id,
                        'old_status' => PaymentConceptStatus::ACTIVO->value,
                        'new_status' => PaymentConceptStatus::FINALIZADO->value
                    ];
                }
            });

        return $updatedConcepts;

    }
}
