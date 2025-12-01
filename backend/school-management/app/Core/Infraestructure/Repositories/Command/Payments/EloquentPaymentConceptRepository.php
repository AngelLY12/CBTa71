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
        return PaymentConceptMapper::toDomain($pc);
    }

    public function update(int $conceptId, array $data): PaymentConcept
    {
        $pc = $this->findOrFail($conceptId);
        $pc->update($data);
        $pc->refresh();
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
        $chunkSize = 50;

        if($replaceRelations){
           $pc->users()->detach();

            foreach (array_chunk($userIds->userIds, $chunkSize) as $chunk) {
                $pc->users()->attach($chunk);
            }
        }else{
            foreach (array_chunk($userIds->userIds, $chunkSize) as $chunk) {
                $pc->users()->syncWithoutDetaching($chunk);
            }
        }
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
        return PaymentConceptMapper::toDomain($pc);

    }


    public function attachToSemester(int $conceptId, array $semesters, bool $replaceRelations=false): PaymentConcept
    {
        $pc = $this->findOrFail($conceptId);
        if ($replaceRelations) {
            $pc->paymentConceptSemesters()->delete();
        }
        foreach ($semesters as $semester) {
        $pc->paymentConceptSemesters()->updateOrCreate(
            [
                'payment_concept_id' => $pc->id,
                'semestre' => $semester
            ],
            ['semestre' => $semester]
        );
    }
        return PaymentConceptMapper::toDomain($pc);
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
     private function findOrFail(int $id): EloquentPaymentConcept
    {
        return EloquentPaymentConcept::findOrFail($id);
    }

    public function cleanDeletedConcepts(): int
    {
        $thresholdDate = Carbon::now()->subDays(30);
        return DB::table('payment_concepts')
            ->where('status', PaymentConceptStatus::ELIMINADO)
            ->where('updated_at', '<', $thresholdDate)
            ->delete();
    }
}
