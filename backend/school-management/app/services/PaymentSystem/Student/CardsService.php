<?php

namespace App\Services\PaymentSystem\Student;

use App\Models\User;
use App\Services\PaymentSystem\StripeService;
use App\Utils\ResponseBuilder;

class CardsService{


    public function savedPaymentMethod(string $paymentMethodId, User $user){
        try {
            $stripeService = new StripeService();
            $stripeService->createStripePaymentMethod($paymentMethodId, $user);

            return (new ResponseBuilder())
                ->success(true)
                ->message('Método de pago creado correctamente')
                ->build();
        } catch (\Exception $e) {
            logger()->error("Error guardando método de pago: " . $e->getMessage());

            return (new ResponseBuilder())
                ->success(false)
                ->message('Error guardando método de pago')
                ->build();
        }
    }

    public function showPaymentMethods(User $user){

        try {
            $stripeService = new StripeService();
            $paymentMethods = $stripeService->showPaymentMethods($user);

            $cards = [];
            foreach ($paymentMethods->data as $pm) {
                $cards[] = [
                    'id'        => $pm->id,
                    'brand'     => $pm->card->brand,
                    'last4'     => $pm->card->last4,
                    'exp_month' => $pm->card->exp_month,
                    'exp_year'  => $pm->card->exp_year,
                    'funding'   => $pm->card->funding,
                    'country'   => $pm->card->country,
                ];
            }

            if(empty($cards)){
                return (new ResponseBuilder())
                ->success(false)
                ->message("No hay métodos de pago guardados")
                ->build();
            }

            return (new ResponseBuilder())
                ->success(true)
                ->data($cards)
                ->build();
        } catch (\Exception $e) {
            logger()->error("Error mostrando métodos de pago: " . $e->getMessage());

            return (new ResponseBuilder())
                ->success(false)
                ->message('Error mostrando métodos de pago')
                ->build();
        }
    }

    public function deletePaymentMethod($stripePaymentMethodId){
        try {
            $stripeService = new StripeService();
            $stripeService->deletePaymentMethod($stripePaymentMethodId);

            return (new ResponseBuilder())
                ->success(true)
                ->message('Método de pago eliminado correctamente')
                ->build();
        } catch (\Exception $e) {
            logger()->error("Error eliminando método de pago: " . $e->getMessage());

            return (new ResponseBuilder())
                ->success(false)
                ->message("Error eliminando método de pago")
                ->build();
        }


    }

}
