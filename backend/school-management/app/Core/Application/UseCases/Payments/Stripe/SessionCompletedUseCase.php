<?php

namespace App\Core\Application\UseCases\Payments\Stripe;

use App\Core\Application\Traits\HasPaymentSession;
use App\Core\Domain\Repositories\Command\UserRepInterface;
use Stripe\Stripe;

class SessionCompletedUseCase
{
    public function __construct(
        private UserRepInterface $userRepo,
        private FinalizeSetupSessionUseCase $finalize
    ) {

    }
    use HasPaymentSession;

    public function execute($obj)
    {
        if (!isset($obj->mode)) {
            logger()->warning("Evento de sesión sin 'mode'. session_id={$obj->id}");
            return true;
        }
        if($obj->mode==='payment'){
            return $this->handlePaymentSession($obj, [
                'payment_intent_id' => $obj->payment_intent,
                'status' => $obj->payment_status,
            ]);
        }
        if($obj->mode==='setup'){
            $user = $this->userRepo->findUserByEmail($obj->customer_email ?? null);
            if (!$user) {
                logger()->error("Usuario no encontrado para session_id={$obj->id}");
                return;
            }
            return $this->finalize->execute($obj->id, $user);
        }
        logger()->info("Sesión ignorada en webhook. session_id={$obj->id}");
        return true;
    }

}
