<?php

namespace App\Core\Domain\Repositories\Command;

use App\Core\Application\DTO\Request\StudentDetail\CreateStudentDetailDTO;
use App\Core\Application\DTO\Request\User\CreateUserDTO;
use App\Core\Application\DTO\Request\User\UpdateUserPermissionsDTO;
use App\Core\Application\DTO\Request\User\UpdateUserRoleDTO;
use App\Core\Application\DTO\Response\User\UserChangedStatusResponse;
use App\Core\Application\DTO\Response\User\UserWithUpdatedRoleResponse;
use App\Core\Domain\Entities\User;

interface UserRepInterface{
    public function create(CreateUserDTO $user):User;
    public function getUserByStripeCustomer(string $customerId): User;
    public function findUserByEmail(string $email):?User;
    public function findById(int $userId):User;
    public function update(User $user, array $fields):User;
    public function changeStatus(array $userIds,string $status): UserChangedStatusResponse;
    public function createToken(User $user, string $name): string;
    public function createRefreshToken(User $user, string $name): string;
    public function revokeToken(string $tokenId): void;
    public function attachStudentDetail(CreateStudentDetailDTO $details): User;
    public function getUserWithStudentDetail(User $user):User;
    public function bulkInsertWithStudentDetails(array $users): int;
    public function updatePermissionToMany(UpdateUserPermissionsDTO $dto): array;
    public function updateRoleToMany(UpdateUserRoleDTO $dto): UserWithUpdatedRoleResponse;
    public function deletionInvalidTokens(): int;
    public function deletionEliminateUsers(): int;
}
