<?php

namespace App\Core\Application\UseCases\Payments\Stripe;

use App\Core\Domain\Entities\PaymentMethod;
use App\Core\Domain\Enum\Cache\CachePrefix;
use App\Core\Domain\Enum\Cache\StudentCacheSufix;
use App\Core\Domain\Repositories\Command\Payments\PaymentMethodRepInterface;
use App\Core\Domain\Repositories\Query\Payments\PaymentMethodQueryRepInterface;
use App\Core\Domain\Repositories\Query\User\UserQueryRepInterface;
use App\Core\Infraestructure\Cache\CacheService;
use Illuminate\Support\Facades\DB;

class PaymentMethodAttachedUseCase
{
    public function __construct(
        private PaymentMethodRepInterface $pmRepo,
        private PaymentMethodQueryRepInterface $pmqRepo,
        private UserQueryRepInterface $userRepo,
        private CacheService $service

    ) {

    }
    public function execute($obj){

        if (!$obj) {
            logger()->error("PaymentMethod no encontrado: {$obj->id}");
            throw new \InvalidArgumentException('El PaymentMethod es nulo.');
        }
        $paymentMethodId = $obj->id;
        $pm=$this->pmqRepo->findByStripeId($paymentMethodId);
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
        $this->service->clearKey(CachePrefix::STUDENT->value, StudentCacheSufix::CARDS->value . ":show:$user->id");
        return true;
    }
}
