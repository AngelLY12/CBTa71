<?php

namespace App\Core\Domain\Repositories\Query\User;

use App\Core\Application\DTO\Response\User\UserIdListDTO;
use App\Core\Domain\Entities\PaymentConcept;
use App\Core\Domain\Entities\User;
use App\Models\User as ModelsUser;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface UserQueryRepInterface{
    public function findUserByEmail(string $email):?User;
    public function findById(int $userId): ?User;
    public function getUserWithStudentDetail(int $userId):User;
    public function getUserByStripeCustomer(string $customerId): User;
    public function getUserIdsByControlNumbers(array $controlNumbers): UserIdListDTO;
    public function countStudents(bool $onlyThisYear): int;
    public function findActiveStudents(?string $search, int $perPage, int $page): LengthAwarePaginator;
    public function findBySearch(string $search): ?User;
    public function getRecipients(PaymentConcept $concept, string $appliesTo): array;
    public function hasRole(int $userId, string $role):bool;
    public function getStudentsWithPendingSummary(array $userIds): array;
    public function findAllUsers(int $perPage, int $page): LengthAwarePaginator;
    public function findAuthUser(): ?User;
    public function findByIds(array $ids): iterable;
    public function findUserRoles(int $userId): array;
    public function findModelEntity(int $userId): ModelsUser;
    public function getUsersByRole(string $role): Collection;
    public function getUsersByCurp(array $curps): Collection;

}
