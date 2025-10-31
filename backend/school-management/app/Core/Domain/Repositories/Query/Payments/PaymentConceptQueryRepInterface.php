<?php

namespace App\Core\Domain\Repositories\Query\Payments;

use App\Core\Application\DTO\Response\PaymentConcept\PendingSummaryResponse;
use App\Core\Domain\Entities\User;
use Illuminate\Pagination\LengthAwarePaginator;

interface PaymentConceptQueryRepInterface{
    //Dashboard Student
    public function getPendingPaymentConcepts(User $user): PendingSummaryResponse;
    public function countOverduePayments(User $user): int;
    //Dashboard Staff
    public function findAllConcepts(string $status, int $perPage, int $page): LengthAwarePaginator;
    public function getAllPendingPaymentAmount(bool $onlyThisYear): PendingSummaryResponse;
    public function getConceptsToDashboard(bool $onlyThisYear,int $perPage, int $page): LengthAwarePaginator;
    public function getPendingPaymentConceptsWithDetails(User $user):array;
    public function getOverduePayments(User $user):array;
    public function getPendingWithDetailsForStudents(array $userIds): array;
    public function finalizePaymentConcepts(): void;
}
