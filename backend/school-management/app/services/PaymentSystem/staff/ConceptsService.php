<?php

namespace App\Services\PaymentSystem\Staff;

use App\Models\PaymentConcept;
use App\Utils\ResponseBuilder;
use App\Utils\Validators\PaymentConceptValidator;
use Illuminate\Support\Facades\DB;

class ConceptsService{


    public function showConcepts(string $status = 'todos'){
        try{
            $paymentConcepts = PaymentConcept::orderBy('created_at','desc');

        switch($status){
            case 'activos':
                $paymentConcepts->where('status','Activo');
                break;
            case 'finalizados':
                $paymentConcepts->where('status','Finalizado');
                break;
            case 'todos':
            default:
                break;

        }
        $conceptsFiltered = $paymentConcepts->get();
        if($conceptsFiltered->isEmpty()){
            return (new ResponseBuilder())
                ->success(false)
                ->message('No hay conceptos registrados')
                ->build();
        }

        return (new ResponseBuilder())
        ->success(true)
        ->data($conceptsFiltered)
        ->build();

        }catch(\Exception $e){
            logger()->error("Error mostrando los conceptos: " . $e->getMessage());

            return (new ResponseBuilder())
                ->success(false)
                ->message('Error mostrando los conceptos registrados')
                ->build();
        }

    }

    public function createPaymentConcept(PaymentConcept $pc, string $appliesTo='todos', ?int $semestre=null, ?string $career=null, array|string|null $students = null)
    {
        DB::beginTransaction();
        try{
            PaymentConceptValidator::ensureConceptHasRequiredFields($pc);
            $paymentConcept = PaymentConcept::create([
                'concept_name' => $pc->concept_name,
                'description' =>$pc->description ?? null,
                'status' => $pc->status,
                'start_date' => $pc->start_date ?? now(),
                'end_date' => $pc->end_date ?? null,
                'amount' => $pc->amount,
                'is_global' => $appliesTo === 'todos'
            ]);

            switch($appliesTo){
                case 'carrera':
                    if ($career) {
                        $paymentConcept->careers()->attach($career);
                    }
                    break;

                case 'semestre':
                    if ($semestre) {
                        $paymentConcept->paymentConceptSemesters()->create([
                            'semestre' => $semestre,
                        ]);
                    }
                    break;

                case 'estudiantes':
                    if ($students) {
                        $ids = is_array($students) ? $students : [$students];
                        $paymentConcept->users()->attach($ids);
                    }
                    break;

                case 'todos':
                default:
                    break;
            }
            DB::commit();

            return (new ResponseBuilder())
                ->success(true)
                ->data($paymentConcept)
                ->build();


        }catch(\Exception $e){
            logger()->error("Error al crear el concepto: " . $e->getMessage());
            DB::rollBack();
            return (new ResponseBuilder())
                ->success(false)
                ->message('Error al crear el concepto')
                ->build();
        }


    }

    public function updatePaymentConcept(PaymentConcept $pc, array $data, ?int $semestre = null, ?string $career = null, array|string|null $students = null)
    {
    DB::beginTransaction();
    try {

        $pc->update([
            'concept_name' => $data['concept_name'] ?? $pc->concept_name,
            'description'  => $data['description'] ?? $pc->description,
            'status'       => $data['status'] ?? $pc->status,
            'start_date'   => $data['start_date'] ?? $pc->start_date,
            'end_date'     => $data['end_date'] ?? $pc->end_date,
            'amount'       => $data['amount'] ?? $pc->amount,
            'is_global'    => $data['is_global'] ?? $pc->is_global,
        ]);

        if (isset($data['applies_to'])) {
            switch($data['applies_to']){
                case 'carrera':
                    if ($career) {
                        $pc->careers()->sync([$career]);
                    }
                    break;

                case 'semestre':
                    if ($semestre) {
                        $pc->paymentConceptSemesters()->updateOrCreate(
                            ['payment_concept_id' => $pc->id, 'semestre' => $semestre],
                            ['semestre' => $semestre]
                        );
                    }
                    break;

                case 'estudiantes':
                    if ($students) {
                        $ids = is_array($students) ? $students : [$students];
                        $pc->users()->sync($ids);
                    }
                    break;

                case 'todos':
                default:
                    break;
            }
        }

        DB::commit();

        return (new ResponseBuilder())
            ->success(true)
            ->data($pc->toArray())
            ->build();

    } catch (\Exception $e) {
        logger()->error("Error al actualizar el concepto: " . $e->getMessage());
        DB::rollBack();
        return (new ResponseBuilder())
            ->success(false)
            ->message('Error al actualizar el concepto')
            ->build();
    }
}

    public function finalizePaymentConcept(PaymentConcept $concept)
    {
        try {
            $concept->update([
                'end_date' => now(),
                'status'   => 'Finalizado',
            ]);

            return (new ResponseBuilder())
                ->success(true)
                ->message('Concepto finalizado correctamente')
                ->data($concept->toArray())
                ->build();

        } catch (\Exception $e) {
            logger()->error("Error finalizando el concepto: " . $e->getMessage());

            return (new ResponseBuilder())
                ->success(false)
                ->message('No se pudo finalizar el concepto')
                ->build();
        }
    }


}
