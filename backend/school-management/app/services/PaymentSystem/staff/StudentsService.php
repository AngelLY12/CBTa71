<?php

namespace App\Services\PaymentSystem\Staff;
use App\Models\User;
use App\Models\PaymentConcept;
use App\Utils\ResponseBuilder;
use Illuminate\Support\Facades\DB;

class StudentsService{


    public function showAllStudents(?string $search=null){
            $studentsQuery = User::role('alumno')->select('id','name','last_name','career_id','semestre');


            if ($search) {
                $studentsQuery->where(function($q) use ($search) {
                    $q->where('name', 'like', "%$search%")
                      ->orWhere('last_name', 'like', "%$search%")
                      ->orWhere('email', 'like', "%$search%");
                });
            }

            $studentsQuery->withCount(['pendingPaymentConcepts as pendientes'])
                      ->withSum(['pendingPaymentConcepts as monto']);

            $students=$studentsQuery->paginate(15);

            $students->getCollection()->transform(function ($student) {

                return [
                    'id'        => $student->id,
                    'nombre'    => $student->name . ' ' . $student->last_name,
                    'semestre'  => $student->semestre,
                    'pendientes'=> $student->pendientes,
                    'monto'     => $student->monto,
                ];
            });

           return $students;

    }

}
