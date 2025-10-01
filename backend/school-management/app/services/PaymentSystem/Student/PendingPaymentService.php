<?php

namespace App\Services\PaymentSystem\Student;
use App\Models\User;
use Stripe\Stripe;
use App\Models\Payment;
use App\Models\PaymentConcept;
use App\Services\PaymentSystem\StripeService;
use App\Utils\ResponseBuilder;
use App\Utils\Validators\PaymentConceptValidator;
use App\Utils\Validators\StripeValidator;
use Illuminate\Support\Facades\DB;


class PendingPaymentService{


    public function showPendingPayments(User $user) {
        try {
            $concepts = PaymentConcept::where('status', 'Activo')
                ->whereDoesntHave('payments', fn($q) => $q->where('user_id', $user->id))
                ->whereDate('start_date', '<=', now())
                ->whereDate('end_date', '>=', now())
                ->where(function($q) use ($user) {
                    $q->where('is_global', true)
                      ->orWhereHas('users', fn($q) => $q->where('users.id', $user->id))
                      ->orWhereHas('careers', fn($q) => $q->where('careers.id', $user->career_id))
                      ->orWhereHas('paymentConceptSemesters', fn($q) => $q->where('semestre', $user->semestre));
                })
                ->get()
                ->map(fn($concept) => [
                    'id'           => $concept->id,
                    'concepto'     => $concept->concept_name,
                    'descripcion'  => $concept->description,
                    'monto'        => $concept->amount,
                    'fecha_inicio' => $concept->start_date,
                    'fecha_fin'    => $concept->end_date,
                ]);

            if ($concepts->isEmpty()) {
                return (new ResponseBuilder())->success(false)
                                             ->message('No hay pagos pendientes')
                                             ->build();
            }

            return (new ResponseBuilder())->success()
                                         ->data($concepts)
                                         ->build();

        } catch (\Exception $e) {
            return (new ResponseBuilder())->success(false)
                                         ->message('Ocurrió un error al obtener los pagos pendientes')
                                         ->build();
        }

    }


    public function payConcept(User $user, int $conceptId, string $paymentMethodId) {
        DB::beginTransaction();

        try {
            $concept = PaymentConcept::findOrFail($conceptId);

            PaymentConceptValidator::ensureConceptIsActiveAndValid($concept);
            StripeValidator::ensureExistsPaymentMethodId($paymentMethodId, $user);

            $stripeService = new StripeService();
            $paymentIntent = $stripeService->createPaymentIntent($user, $concept, $paymentMethodId);

            $charge = $paymentIntent->charges->data[0] ?? null;
            $last4 = $charge?->payment_method_details?->card?->last4 ?? null;
            $brand = $charge?->payment_method_details?->card?->brand ?? null;
            $typePaymentMethod = $charge?->payment_method_details?->type ?? null;

            $payment = Payment::create([
                'user_id' => $user->id,
                'payment_concept_id' => $concept->id,
                'payment_intent_id' => $paymentIntent->id,
                'stripe_payment_method_id' => $paymentMethodId,
                'last4' => $last4,
                'brand' => $brand,
                'type_payment_method' => $typePaymentMethod,
                'status' => $paymentIntent->status,
                'url' => $charge->receipt_url ?? null
            ]);

            DB::commit();

            return (new ResponseBuilder())
                ->success(true)
                ->message('Pago realizado correctamente')
                ->data($payment)
                ->build();

        }catch (\InvalidArgumentException $e) {
            return (new ResponseBuilder())
                ->success(false)
                ->message($e->getMessage())
                ->build();
        }catch (\Stripe\Exception\CardException $e) {
            return (new ResponseBuilder())
                ->success(false)
                ->message('Tu tarjeta fue rechazada: ' . $e->getError()->message)
                ->build();
        } catch (\Stripe\Exception\RateLimitException $e) {
            return (new ResponseBuilder())
                ->success(false)
                ->message('Demasiadas solicitudes, intenta más tarde.')
                ->build();
        } catch (\Exception $e) {
            DB::rollBack();
            return (new ResponseBuilder())
                ->success(false)
                ->message('Ocurrió un error al procesar el pago: ' . $e->getMessage())
                ->build();
        }
    }

}
