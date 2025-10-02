<?php

namespace App\Services\PaymentSystem\Staff;

use App\Models\PaymentConcept;
use App\Models\User;
use Stripe\PaymentIntent;
use App\Utils\ResponseBuilder;

class DebtsService{

    public function showAllpendingPayments(?string $search=null)
    {
            $query = PaymentConcept::with('payments.user')
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
                        'concepto'  => $concept->concept_name,
                        'monto'     => $concept->amount,
                        'nombre'    => $payment->user->name . ' ' . $payment->user->last_name,
                    ];
                });
            });

            return $paginated;

    }

    public function validatePayment(string $search, string $payment_intent_id)
{
    $student = User::where('curp', 'like', "%$search%")
        ->orWhere('email', 'like', "%$search%")
        ->orWhere('n_control', 'like', "%$search%")
        ->first();

    if (!$student) {
        throw new \InvalidArgumentException('Alumno no encontrado');

    }

    $payment = $student->payments()->where('payment_intent_id', $payment_intent_id)->first();

    if (!$payment) {
            $intent = PaymentIntent::retrieve($payment_intent_id);
            $charge = $intent->charges->data[0] ?? null;

            if (!$charge) {
                throw new \InvalidArgumentException('Pago no encontrado en Stripe');

            }

            $payment = $student->payments()->create([
                'user_id'=>$student->id,
                'payment_concept_id',
                'payment_method_id'=> $charge->payment_method ?? null,
                'payment_intent_id' => $payment_intent_id,
                'status' => $intent->status,
                'url'=>$paymentIntent->charges->data[0]->receipt_url ?? null
            ]);


    }

    $data = [
        'student' => [
            'id' => $student->id,
            'nombre' => $student->name . ' ' . $student->last_name,
            'email' => $student->email,
            'curp' => $student->curp,
            'n_control' => $student->n_control
        ],
        'payment' => [
            'id' => $payment->id,
            'amount' => $payment->amount,
            'status' => $payment->status,
            'payment_intent_id' => $payment->payment_intent_id
        ]
    ];

    return $data;
}



}
