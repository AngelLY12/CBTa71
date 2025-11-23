<?php

namespace App\Core\Domain\Repositories\Command;

use App\Core\Application\DTO\Request\StudentDetail\CreateStudentDetailDTO;
use App\Core\Application\DTO\Request\User\CreateUserDTO;
use App\Core\Application\DTO\Response\User\UserChangedStatusResponse;
use App\Core\Domain\Entities\User;

interface UserRepInterface{
    public function create(CreateUserDTO $user):User;
    public function update(int $userId, array $fields):User;
    public function changeStatus(array $userIds,string $status): UserChangedStatusResponse;
    public function bulkInsertWithStudentDetails(array $users): array;
    public function deletionEliminateUsers(): int;
    public function attachStudentDetail(CreateStudentDetailDTO $detail): User;
    public function createToken(int $userId, string $name): string;
    public function createRefreshToken(int $userId, string $name): string;

}
