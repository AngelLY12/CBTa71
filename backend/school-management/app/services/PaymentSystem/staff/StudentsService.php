<?php

namespace App\Services\PaymentSystem\Staff;
use App\Models\User;
use App\Models\PaymentConcept;
use App\Utils\ResponseBuilder;
use Illuminate\Support\Facades\DB;

class StudentsService{


    public function showAllStudents(?string $search=null){
            $studentsQuery = User::role('student')->select('id','name','last_name','career_id','semestre')
            ->where('status','activo');


            if ($search) {
                $studentsQuery->where(function($q) use ($search) {
                    $q->where('name', 'like', "%$search%")
                      ->orWhere('last_name', 'like', "%$search%")
                      ->orWhere('email', 'like', "%$search%");
                });
            }

             $studentsQuery->with(['paymentConcepts' => function($q) {
                $q->pendingPaymentConcept()
                ->select('id', 'amount', 'user_id');;
            }]);

            $students=$studentsQuery->paginate(15);

            $students->getCollection()->transform(function ($student) {

                return [
                    'id'        => $student->id,
                    'nombre'    => $student->name . ' ' . $student->last_name,
                    'semestre'  => $student->semestre,
                    'pendientes'=> $$student->paymentConcepts->count(),
                    'monto'     => $student->paymentConcepts->sum('amount'),
                ];
            });

           return $students;

    }

}
