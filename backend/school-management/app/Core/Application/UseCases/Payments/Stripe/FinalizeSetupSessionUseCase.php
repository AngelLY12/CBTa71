<?php
namespace App\Core\Application\UseCases\Payments\Stripe;

use App\Core\Application\DTO\Response\PaymentMethod\DisplayPaymentMethodResponse;
use App\Core\Application\Mappers\PaymentMethodMapper;
use App\Core\Domain\Entities\PaymentMethod;
use App\Core\Domain\Entities\User;
use App\Core\Domain\Repositories\Command\Payments\PaymentMethodRepInterface;
use App\Core\Domain\Repositories\Command\Stripe\StripeGatewayInterface;
use App\Core\Infraestructure\Cache\CacheService;
use Illuminate\Support\Facades\DB;

class FinalizeSetupSessionUseCase
{
    public function __construct(
        private PaymentMethodRepInterface $pmRepo,
        private StripeGatewayInterface $stripe,
        private CacheService $service
    ) {

    }
    public function execute(string $sessionId, User $user): DisplayPaymentMethodResponse
    {
         $setupIntent = $this->stripe->getSetupIntentFromSession($sessionId);
         $pm = $this->stripe->retrievePaymentMethod($setupIntent->payment_method);

            $paymentMethod = new PaymentMethod(
                user_id: $user->id,
                stripe_payment_method_id: $pm->id,
                brand: $pm->card->brand ?? null,
                last4: $pm->card->last4 ?? null,
                exp_month: $pm->card->exp_month ?? null,
                exp_year: $pm->card->exp_year ?? null
            );

            $pm= DB::transaction(function() use ($paymentMethod) {
                return $this->pmRepo->create($paymentMethod);
            });
            $this->service->clearPrefix("student:cards:show:$user->id");
        return PaymentMethodMapper::toDisplayPaymentMethodResponse($pm);
    }

}
