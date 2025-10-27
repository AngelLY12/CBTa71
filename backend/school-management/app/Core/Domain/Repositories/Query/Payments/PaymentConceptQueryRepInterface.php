<?php

namespace App\Core\Domain\Repositories\Query\Payments;

use App\Core\Application\DTO\Response\PaymentConcept\PendingSummaryResponse;
use App\Core\Domain\Entities\User;

interface PaymentConceptQueryRepInterface{
    //Dashboard Student
    public function getPendingPaymentConcepts(User $user): PendingSummaryResponse;
    public function countOverduePayments(User $user): int;
    //Dashboard Staff
    public function findAllConcepts(string $status = 'todos'): array;
    public function getAllPendingPaymentAmount(bool $onlyThisYear = false): PendingSummaryResponse;
    public function getConceptsToDashboard(bool $onlyThisYear = false): array;
    public function getPendingPaymentConceptsWithDetails(User $user):array;
    public function getOverduePayments(User $user):array;
    public function getPendingWithDetailsForStudents(array $userIds): array;
    public function getStudentsWithPendingSummary(array $userIds): array;
    public function finalizePaymentConcepts(): void;
}
