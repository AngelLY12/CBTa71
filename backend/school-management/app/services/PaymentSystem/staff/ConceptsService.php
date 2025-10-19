<?php

namespace App\Services\PaymentSystem\Staff;

use App\Jobs\SendMailJob;
use App\Mail\NewConceptMail;
use App\Models\Career;
use App\Models\PaymentConcept;
use App\Models\User;
use Illuminate\Validation\ValidationException;
use App\Utils\Validators\PaymentConceptValidator;
use Illuminate\Support\Facades\DB;
use App\Notifications\NewConceptNotification;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;

class ConceptsService{


    public function showConcepts(string $status = 'todos'){
            $paymentConcepts = PaymentConcept::select('concept_name',
            'description',
            'status',
            'start_date',
            'end_date',
            'amount',
            'is_global')
            ->orderBy('created_at','desc');

        switch($status){
            case 'activos':
                $paymentConcepts->where('status','activo');
                break;
            case 'finalizados':
                $paymentConcepts->where('status','finalizado');
                break;
            case 'Desactivados':
                $paymentConcepts->where('status','desactivado');
                break;
            case 'todos':
            default:
                break;

        }
;
        return $paymentConcepts->get();

    }

    public function createPaymentConcept(PaymentConcept $pc, string $appliesTo='todos', ?int $semestre=null, ?string $career=null, array|string|null $students = null)
    {
        $paymentConcept= DB::transaction(function() use ($pc, $appliesTo, $semestre, $career, $students){
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
                    $careerModel = Career::where('career_name', $career)->first();
                    if ($careerModel) {
                        $paymentConcept->careers()->attach($careerModel->id);
                    } else {
                        throw ValidationException::withMessages(['career'=>"La carrera '$career' no existe"]);
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
                    $ids = User::whereIn('n_control', $students)->where('status','activo')->pluck('id');
                    if ($ids->isNotEmpty()) {
                        $paymentConcept->users()->attach($ids);
                    } else {
                        throw ValidationException::withMessages([
                            'students' => ['Ninguno de los estudiantes existe o está dado de baja.']
                        ]);                    }
                    }
                    break;

                case 'todos':
                default:
                    break;
            }

            return $paymentConcept;

         });
         $recipients = match($appliesTo){
                'carrera' => $paymentConcept->careers()->with('users')->get()->pluck('users')->flatten(),
                'semestre' => $paymentConcept->paymentConceptSemesters()->with('users')->get()->pluck('users')->flatten(),
                'estudiantes' => $paymentConcept->users,
                'todos' => User::where('status','activo')->get(),
            };

            foreach($recipients as $user) {
                $data = [
                    'concept_name' => $paymentConcept->concept_name,
                    'amount' => $paymentConcept->amount,
                    'end_date' => $paymentConcept->end_date ? $paymentConcept->end_date->format('d/m/Y') : null,
                ];
                $mail = new NewConceptMail($data, $user->name, $user->email);
                SendMailJob::dispatch($mail, $user->email);

            }

        return $paymentConcept;
    }

    public function updatePaymentConcept(PaymentConcept $pc, array $data, ?int $semestre = null, ?string $career = null, array|string|null $students = null)
    {
         $pc= DB::transaction(function() use ($pc, $data, $semestre, $career, $students){

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
                    $careerModel = Career::where('career_name', $career)->first();
                    if ($careerModel) {
                        $pc->careers()->sync([$careerModel->id]);
                    } else {
                            throw ValidationException::withMessages(['career'=>"La carrera '$career' no existe"]);
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
                        $ids = User::whereIn('n_control', $students)->where('status','activo')->pluck('id');
                    if ($ids->isNotEmpty()) {
                        $pc->users()->attach($ids);
                    } else {
                        throw ValidationException::withMessages([
                            'students' => ['Ninguno de los estudiantes existe o está dado de baja.']
                        ]);                    }
                    }
                    break;

                case 'todos':
                default:
                    break;
            }
        }

        return $pc;
    });
    $recipients = match($data['applies_to']){
                'carrera' => $pc->careers()->with('users')->get()->pluck('users')->flatten(),
                'semestre' => $pc->paymentConceptSemesters()->with('users')->get()->pluck('users')->flatten(),
                'estudiantes' => $pc->users,
                'todos' => User::where('status','activo')->get(),
            };

                foreach($recipients as $user) {
                $data = [
                    'concept_name' => $pc->concept_name,
                    'amount' => $pc->amount,
                    'end_date' => $pc->end_date ? $pc->end_date->format('d/m/Y') : null,
                ];
                $mail = new NewConceptMail($data, $user->name, $user->email);
                SendMailJob::dispatch($mail, $user->email);

            }

    return $pc;

}

    public function finalizePaymentConcept(PaymentConcept $concept)
    {
            $concept->update([
                'end_date' => now(),
                'status'   => 'finalizado',
            ]);

            return $concept;
    }

    public function disablePaymentConcept(PaymentConcept $concept)
    {
            $concept->update([
                'status'   => 'desactivado',
            ]);

            return $concept;
    }

    public function eliminatePaymentConcept(PaymentConcept $concept){
        $concept->update([
            'status' => 'eliminado'
        ]);
        return $concept;
    }


}
