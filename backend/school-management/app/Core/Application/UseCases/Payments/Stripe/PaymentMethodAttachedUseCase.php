<?php

namespace App\Core\Application\UseCases\Payments\Stripe;

use App\Core\Domain\Entities\PaymentMethod;
use App\Core\Domain\Repositories\Command\Payments\PaymentMethodRepInterface;
use App\Core\Domain\Repositories\Command\UserRepInterface;
use Illuminate\Support\Facades\DB;
use Stripe\Stripe;

class PaymentMethodAttachedUseCase
{
    public function __construct(
        private PaymentMethodRepInterface $pmRepo,
        private UserRepInterface $userRepo,

    ) {
        Stripe::setApiKey(config('services.stripe.secret'));

    }
    public function execute($obj){

        if (!$obj) {
            logger()->error("PaymentMethod no encontrado: {$obj->id}");
            throw new \InvalidArgumentException('El PaymentMethod es nulo.');
        }
        $paymentMethodId = $obj->id;
        $pm=$this->pmRepo->findByStripeId($paymentMethodId);
        if ($pm) {
            logger()->info("El método de pago {$paymentMethodId} ya existe");
            return false;
        }
        $user = $this->userRepo->getUserByStripeCustomer($obj->customer);
        $pmDomain = new PaymentMethod(
            user_id: $user->id,
            stripe_payment_method_id: $paymentMethodId,
            brand: $obj->card->brand,
            last4:  $obj->card->last4,
            exp_month:  $obj->card->exp_month,
            exp_year: $obj->card->exp_year,
        );
        DB::transaction(function() use ($pmDomain) {
            $this->pmRepo->create($pmDomain);

        });
        return true;
    }
}
