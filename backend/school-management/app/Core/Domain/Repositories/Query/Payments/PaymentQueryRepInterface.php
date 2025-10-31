<?php

namespace App\Core\Domain\Repositories\Query\Payments;

use App\Core\Application\DTO\Request\Mail\PaymentCreatedEmailDTO;
use App\Core\Domain\Entities\Payment;
use App\Core\Domain\Entities\PaymentMethod;
use App\Core\Domain\Entities\User;
use Generator;
use Illuminate\Pagination\LengthAwarePaginator;

interface PaymentQueryRepInterface{
    public function sumPaymentsByUserYear(User $user): int;
    public function getPaymentHistory(User $user, int $perPage, int $page): LengthAwarePaginator;
    //Dashboard Staff
    public function getAllPaymentsMade(bool $onlyThisYear):int;
    //Others
    public function getPaymentHistoryWithDetails(User $user, int $perPage, int $page): LengthAwarePaginator;
    public function findByIntentOrSession(int $userId, string $paymentIntentId): ?Payment;
//    public function getAllWithSearch(?string $search = null, int $perPage = 15): LengthAwarePaginator;
    public function getPaidWithinLastMonthCursor(): Generator;
    public function updatePaymentWithStripeData(Payment $payment, $pi, $charge,PaymentMethod $savedPaymentMethod): void;
    public function getAllWithSearchEager(?string $search, int $perPage,int $page): LengthAwarePaginator;
    public function getConceptNameFromPayment(string $paymentIntentId): ?string;

}
