<?php

namespace App\Services\PaymentSystem\Staff;
use App\Models\PaymentConcept;
use App\Utils\ResponseBuilder;

class PaymentsService{

    public function showAllPayments(?string $search = null)
    {
        try{
            $query = PaymentConcept::with(['payments.user'])
            ->when($search, function ($q) use ($search) {
                $q->where('concept_name', 'like', "%$search%")
                  ->orWhereHas('payments.user', function ($sub) use ($search) {
                      $sub->where('name', 'like', "%$search%")
                          ->orWhere('last_name', 'like', "%$search%")
                          ->orWhere('email', 'like', "%$search%");
                  });
            });

        $paginated = $query->paginate(15);

        $paginated->getCollection()->transform(function ($concept) {
            return $concept->payments->map(function ($payment) use ($concept) {

                return [
                    'fecha'     => $payment->created_at,
                    'concepto'  => $concept->concept_name,
                    'monto'     => $concept->amount,
                    'metodo'    => $payment->type_payment_method,
                    'nombre'    => $payment->user->name . ' ' . $payment->user->last_name,
                ];
            });
        });

        if($paginated->isEmpty()){
            return (new ResponseBuilder())
            ->success(false)
            ->message('No hay pagos registrados')
            ->build();

        }

        return (new ResponseBuilder())
        ->success(true)
        ->data($paginated->toArray())
        ->build();

        }catch(\Exception $e){
            logger()->error("Error mostrando métodos de pago: " . $e->getMessage());

            return (new ResponseBuilder())
                ->success(false)
                ->message('Error mostrando todos los pagos registrados')
                ->build();


        }

    }

}
