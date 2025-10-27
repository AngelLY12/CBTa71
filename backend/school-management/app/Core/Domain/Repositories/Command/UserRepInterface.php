<?php

namespace App\Core\Domain\Repositories\Command;

use App\Core\Application\DTO\Request\StudentDetail\CreateStudentDetailDTO;
use App\Core\Domain\Entities\User;

interface UserRepInterface{
    public function create(User $user):User;
    public function getUserByStripeCustomer(string $customerId): User;
    public function findUserByEmail(string $email):?User;
    public function findById(int $userId):User;
    public function update(User $user, array $fields):User;
    public function createToken(User $user, string $name): string;
    public function attachStudentDetail(CreateStudentDetailDTO $details): User;
    public function getUserWithStudentDetail(User $user):User;
}
