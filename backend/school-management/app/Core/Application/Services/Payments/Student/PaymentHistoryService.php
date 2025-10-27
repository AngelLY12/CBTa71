<?php
namespace App\Core\Application\Services\Payments\Student;

use App\Core\Application\UseCases\Payments\Student\PaymentHistory\GetPaymentHistoryUseCase;
use App\Core\Domain\Entities\User;

class PaymentHistoryService {

    public function __construct(
        private GetPaymentHistoryUseCase $history
    ) {}

    public function paymentHistory(User $user): array {
        return $this->history->execute($user);
    }

}
