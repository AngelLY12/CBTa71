<?php

namespace App\Services\PaymentSystem\Staff;

use App\Models\PaymentConcept;
use App\Models\PaymentMethod;
use App\Models\User;
use App\Notifications\PaymentCreatedNotification;
use App\Notifications\PaymentValidatedNotification;
use Stripe\PaymentIntent;
use Illuminate\Support\Facades\DB;

class DebtsService{

    public function showAllpendingPayments(?string $search=null)
    {
        $studentsQuery = User::role('student')->select('id','name','last_name','email')
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
         ->select('id', 'concept_name', 'amount', 'user_id');
        }]);

        $students=$studentsQuery->paginate(15);

        $students->getCollection()->transform(function ($student) {
            return $student->paymentConcepts->map(fn($concept) => [
                    'id'       => $student->id,
                    'nombre'   => $student->name . ' ' . $student->last_name,
                    'concepto' => $concept->concept_name,
                    'monto'    => $concept->amount,
                ]);
            });

        $students->setCollection($students->getCollection()->flatten(1));

        return $students;
    }

    public function validatePayment(string $search, string $payment_intent_id)
{
        return DB::transaction(function () use ($search, $payment_intent_id) {
                $student = User::select('id','name','last_name','email','curp','n_control')
                ->where('curp', 'like', "%$search%")
                ->orWhere('email', 'like', "%$search%")
                ->orWhere('n_control', 'like', "%$search%")
                ->first();

            if (!$student) {
                throw new \InvalidArgumentException('Alumno no encontrado');

            }

            $payment = $student->payments()->select('id','amount','status','payment_intent_id')
            ->where('payment_intent_id', $payment_intent_id)
            ->orWhere('stripe_session_id', $payment_intent_id)
            ->first();

            if (!$payment) {
                    $intent = PaymentIntent::retrieve($payment_intent_id);
                    $charge = $intent->charges->data[0] ?? null;
                    $paymentConceptId = $intent->metadata->payment_concept_id ?? null;
                    $pm = $charge->payment_method ? PaymentMethod::where('stripe_payment_method_id', $charge->payment_method)->first() : null;
                    $pmId = $pm ? $pm->id : null;
                    $spei=$charge->payment_method_details->bank_transfer->reference_number ?? null;
                    $instructions=$intent->next_action->display_bank_transfer_instructions->hosted_instructions_url ?? null;
                    $voucher=$charge->payment_method_details->oxxo->number ?? null;

                    if (!$charge) {
                        throw new \InvalidArgumentException('Pago no encontrado en Stripe');

                    }

                    $payment = $student->payments()->create([
                        'user_id'=>$student->id,
                        'payment_concept_id'=>$paymentConceptId,
                        'payment_method_id' => $pmId,
                        'stripe_payment_method_id' => $charge->payment_method ?? null,
                        'last4' => $charge->payment_method_details->card->last4 ?? null,
                        'brand' => $charge->payment_method_details->card->brand ?? null,
                        'voucher_number' =>$voucher,
                        'spei_reference' => $spei,
                        'instructions_url' =>$instructions,
                        'payment_intent_id' => $payment_intent_id,
                        'type_payment_method' => $charge->payment_method_details->type ?? null,
                        'status' => $intent->status,
                        'url' => $intent->charges->data[0]->receipt_url ?? null
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
            $payment->user->notify((new PaymentValidatedNotification($payment))->delay(now()->addSeconds(5)));

            return $data;
        });


}



}
