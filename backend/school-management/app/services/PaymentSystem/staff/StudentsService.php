<?php

namespace App\Services\PaymentSystem\Staff;
use App\Models\User;
use App\Models\PaymentConcept;
use App\Utils\ResponseBuilder;

class StudentsService{


    public function showAllStudents(?string $search=null){
        try{
            $studentsQuery = User::role('alumno');


            if ($search) {
                $studentsQuery->where(function($q) use ($search) {
                    $q->where('name', 'like', "%$search%")
                      ->orWhere('last_name', 'like', "%$search%")
                      ->orWhere('email', 'like', "%$search%");
                });
            }

            $students=$studentsQuery->paginate(15);

            $students->getCollection()->transform(function ($student) {
                $conceptosPendientes = PaymentConcept::where('status', 'Activo')
                    ->whereDoesntHave('payments', fn($q) => $q->where('user_id', $student->id))
                    ->where(function ($q) use ($student) {
                        $q->where('is_global', true)
                          ->orWhereHas('users', fn($q) => $q->where('users.id', $student->id))
                          ->orWhereHas('careers', fn($q) => $q->where('careers.id', $student->career_id))
                          ->orWhereHas('paymentConceptSemesters', fn($q) => $q->where('semestre', $student->semestre));
                    })
                    ->get();



                return [
                    'id'        => $student->id,
                    'nombre'    => $student->name . ' ' . $student->last_name,
                    'semestre'  => $student->semestre,
                    'pendientes'=> $conceptosPendientes->count(),
                    'monto'     => $conceptosPendientes->sum('amount'),
                ];
            });

            if($students->isEmpty()){
                return (new ResponseBuilder())
                ->success(false)
                ->message('No hay estudiantes registrados')
                ->build();
            }

            return (new ResponseBuilder())
            ->success(true)
            ->data($students)
            ->build();

        }catch (\Exception $e) {
            logger()->error("Error al mostrar alumnos: " . $e->getMessage());

            return (new ResponseBuilder())
                ->success(false)
                ->message('Error mostrando alumnos')
                ->build();
        }


    }

}
