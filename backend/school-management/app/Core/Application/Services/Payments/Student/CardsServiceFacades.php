<?php

namespace App\Core\Application\Services\Payments\Student;

use App\Core\Application\DTO\Response\PaymentMethod\SetupCardResponse;
use App\Core\Application\Traits\HasCache;
use App\Core\Application\UseCases\Payments\Student\Cards\DeletePaymentMethoUseCase;
use App\Core\Application\UseCases\Payments\Student\Cards\GetUserPaymentMethodsUseCase;
use App\Core\Application\UseCases\Payments\Student\Cards\SetupCardUseCase;
use App\Core\Domain\Entities\User;
use App\Core\Domain\Enum\Cache\CachePrefix;
use App\Core\Domain\Enum\Cache\StudentCacheSufix;
use App\Core\Infraestructure\Cache\CacheService;

class CardsServiceFacades
{

    use HasCache;
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
        $this->service->clearKey(CachePrefix::STUDENT->value, StudentCacheSufix::CARDS->value . ":show:$user->id");
        return $setup;
    }
    public function deletePaymentMethod(User $user, string $paymentMethodId): bool
    {
        $delete=$this->delete->execute($paymentMethodId);
        $this->service->clearKey(CachePrefix::STUDENT->value, StudentCacheSufix::CARDS->value . ":show:$user->id");
        return $delete;
    }

    public function getUserPaymentMethods(int $userId, bool $forceRefresh): array
    {
        $key = $this->service->makeKey(CachePrefix::STUDENT->value, StudentCacheSufix::CARDS->value . ":show:$userId");
        return $this->cache($key,$forceRefresh ,fn() =>$this->show->execute($userId));
    }
}
