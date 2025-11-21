<?php

namespace App\Core\Domain\Repositories\Query\Payments;

use App\Core\Application\DTO\Request\Mail\PaymentCreatedEmailDTO;
use App\Core\Domain\Entities\Payment;
use App\Core\Domain\Entities\PaymentMethod;
use App\Core\Domain\Entities\User;
use Generator;
use Illuminate\Pagination\LengthAwarePaginator;

interface PaymentQueryRepInterface{
    public function findBySessionId(string $sessionId): ?Payment;
    public function findById(int $id): ?Payment;
    public function findByIntentId(string $intentId): ?Payment;
    public function sumPaymentsByUserYear(int $userId): string;
    public function getPaymentHistory(int $userId, int $perPage, int $page): LengthAwarePaginator;
    //Dashboard Staff
    public function getAllPaymentsMade(bool $onlyThisYear):string;
    //Others
    public function getPaymentHistoryWithDetails(int $userId, int $perPage, int $page): LengthAwarePaginator;
    public function findByIntentOrSession(int $userId, string $paymentIntentId): ?Payment;
//    public function getAllWithSearch(?string $search = null, int $perPage = 15): LengthAwarePaginator;
    public function getPaidWithinLastMonthCursor(): Generator;
    public function getAllWithSearchEager(?string $search, int $perPage,int $page): LengthAwarePaginator;
}
