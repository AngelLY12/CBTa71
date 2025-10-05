<?php

namespace App\Utils\Validators;

use App\Models\User;
use App\Models\PaymentConcept;
use App\Services\PaymentSystem\StripeService;
use App\Exceptions\PaymentMethodNotSupportedException;


class StripeValidator{

    public static function ensureUserHasStripeCustomer(User $user): void
    {
        if (empty($user->stripe_customer_id)) {
            throw new \InvalidArgumentException("El usuario no tiene un cliente de Stripe asociado.");
        }
    }

    public static function ensureValidPaymentMethodId(?string $paymentMethodId): void
    {
        if (empty($paymentMethodId)) {
            throw new PaymentMethodNotSupportedException("El ID del método de pago es inválido.");
        }
        if (!preg_match('/^pm_[a-zA-Z0-9]+$/', $paymentMethodId)) {
        throw new PaymentMethodNotSupportedException("El ID del método de pago no tiene un formato válido.");
    }
    }

    public static function ensureValidConcept(PaymentConcept $concept): void
    {
        if (!$concept || $concept->amount <= 0) {
            throw new \InvalidArgumentException("El concepto de pago es inválido.");
        }
    }

    public static function ensureUserHasEmailAndName(User $user): void
    {
        if (empty($user->email) || empty($user->name)) {
            throw new \InvalidArgumentException("El usuario debe tener email y nombre para ser registrado en Stripe.");
        }
    }

    public static function ensureExistsPaymentMethodId(string $paymentMethodId, User $user)
    {
        $stripeService = new StripeService();
        $paymentMethods = $stripeService->showPaymentMethods($user);

        $exists = collect($paymentMethods->data)->contains(fn($pm) => $pm->id === $paymentMethodId);

        if (!$exists) {
            throw new PaymentMethodNotSupportedException('El método de pago no es válido o no pertenece al usuario.');
        }
    }



}
