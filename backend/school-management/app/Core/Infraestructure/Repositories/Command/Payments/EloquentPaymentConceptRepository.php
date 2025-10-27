<?php
namespace App\Core\Infraestructure\Repositories\Command\Payments;

use App\Core\Application\DTO\Response\User\UserIdListDTO;
use App\Core\Domain\Entities\PaymentConcept;
use App\Core\Domain\Repositories\Command\Payments\PaymentConceptRepInterface;
use App\Core\Infraestructure\Mappers\PaymentConceptMapper;
use App\Models\PaymentConcept as EloquentPaymentConcept;

class EloquentPaymentConceptRepository implements PaymentConceptRepInterface {


    public function findById(int $id): ?PaymentConcept
    {
        return optional(EloquentPaymentConcept::find($id), fn($pc) => PaymentConceptMapper::toDomain($pc));
    }

    public function create(PaymentConcept $concept): PaymentConcept
    {
        $pc = EloquentPaymentConcept::create(
        PaymentConceptMapper::toPersistence($concept)
        );
        $pc->refresh();
        return PaymentConceptMapper::toDomain($pc);
    }

    public function update(PaymentConcept $concept, array $data): PaymentConcept
    {
        $pc = $this->findOrFail($concept->id);
        $pc->update($data);
        return PaymentConceptMapper::toDomain($pc);
    }

    public function finalize(PaymentConcept $concept): PaymentConcept
    {
        return $this->update($concept, [
            'end_date' => now(),
            'status'   => 'finalizado',
        ]);
    }

    public function activate(PaymentConcept $concept): PaymentConcept
    {
        return $this->update($concept,[
            'status'   => 'activo',
            'end_date' => null,
        ]);
    }

    public function disable(PaymentConcept $concept): PaymentConcept
    {
        return $this->update($concept, ['status' => 'desactivado']);
    }

    public function deleteLogical(PaymentConcept $concept): PaymentConcept
    {
        return $this->update($concept, ['status' => 'eliminado']);
    }

    public function delete(PaymentConcept $concept): void
    {
        $pc = $this->findOrFail($concept->id);
        $pc->delete();
    }

    public function attachToUsers(PaymentConcept $concept, UserIdListDTO $userIds, bool $replaceRelations=false): PaymentConcept
    {
        $pc = $this->findOrFail($concept->id);
        if($replaceRelations){
            $pc->users()->sync($userIds->userIds);
        }else{
            $pc->users()->syncWithoutDetaching($userIds->userIds);
        }
        return PaymentConceptMapper::toDomain($pc);

    }

    public function attachToCareer(PaymentConcept $concept, array $careerIds, bool $replaceRelations=false): PaymentConcept
    {
        $pc = $this->findOrFail($concept->id);
        if($replaceRelations){
            $pc->careers()->sync($careerIds);
        }else{
            $pc->careers()->syncWithoutDetaching($careerIds);
        }
        return PaymentConceptMapper::toDomain($pc);

    }


    public function attachToSemester(PaymentConcept $concept, array $semesters, bool $replaceRelations=false): PaymentConcept
    {
        $pc = $this->findOrFail($concept->id);
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

    public function detachFromSemester(PaymentConcept $concept): void
    {
        $this->findOrFail($concept->id)->paymentConceptSemesters()->delete();
    }

    public function detachFromCareer(PaymentConcept $concept): void
    {
        $this->findOrFail($concept->id)->careers()->detach();
    }

    public function detachFromUsers(PaymentConcept $concept): void
    {
        $this->findOrFail($concept->id)->users()->detach();
    }
     private function findOrFail(int $id): EloquentPaymentConcept
    {
        return EloquentPaymentConcept::findOrFail($id);
    }
}
