<?php

namespace App\Core\Domain\Repositories\Query;

use App\Core\Application\DTO\Response\User\UserIdListDTO;
use App\Core\Domain\Entities\PaymentConcept;
use App\Core\Domain\Entities\User;
use Illuminate\Pagination\LengthAwarePaginator;

interface UserQueryRepInterface{
    public function getUserIdsByControlNumbers(array $controlNumbers): UserIdListDTO;
    public function countStudents(bool $onlyThisYear): int;
    public function findActiveStudents(?string $search, int $perPage, int $page): LengthAwarePaginator;
    public function findBySearch(string $search): ?User;
    public function getRecipients(PaymentConcept $concept, string $appliesTo): array;
    public function hasRole(User $user, string $role):bool;
    public function getStudentsWithPendingSummary(array $userIds): array;


}
