<?php

namespace App\Core\Application\Services\Payments\Student;

use App\Core\Application\DTO\Response\PaymentMethod\SetupCardResponse;
use App\Core\Application\Traits\HasCache;
use App\Core\Application\UseCases\Payments\Student\Cards\DeletePaymentMethoUseCase;
use App\Core\Application\UseCases\Payments\Student\Cards\GetUserPaymentMethodsUseCase;
use App\Core\Application\UseCases\Payments\Student\Cards\SetupCardUseCase;
use App\Core\Domain\Entities\User;
use App\Core\Infraestructure\Cache\CacheService;

class CardsServiceFacades
{

    use HasCache;
    private string $prefix='cards';
    public function __construct(
        private SetupCardUseCase $setup,
        private DeletePaymentMethoUseCase $delete,
        private GetUserPaymentMethodsUseCase $show,
        private CacheService $service
    )
    {
    }
    public function setupCard(User $user): SetupCardResponse
    {
        $setup= $this->setup->execute($user);
        $this->service->forget("$this->prefix:show:$user->id");
        return $setup;
    }
    public function deletePaymentMethod(User $user, string $paymentMethodId): bool
    {
        $delete=$this->delete->execute($paymentMethodId);
        $this->service->forget("$this->prefix:show:$user->id");
        return $delete;
    }

    public function getUserPaymentMethods(int $userId, bool $forceRefresh): array
    {
        $key="$this->prefix:show:$userId";
        return $this->cache($key,$forceRefresh ,fn() =>$this->show->execute($userId));
    }
}
