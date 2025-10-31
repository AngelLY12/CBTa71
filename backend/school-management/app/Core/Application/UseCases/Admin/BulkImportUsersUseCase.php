<?php

namespace App\Core\Application\UseCases\Admin;

use App\Core\Domain\Repositories\Command\UserRepInterface;

class BulkImportUsersUseCase
{
    public function __construct(
        private UserRepInterface $userRepo
    ) {}

    public function execute(array $rows): void
    {
        $chunks = array_chunk($rows, 200);

        foreach ($chunks as $chunk) {
            try {
                $this->userRepo->bulkInsertWithStudentDetails($chunk);
            } catch (\Throwable $e) {
                logger()->error('Error importing users: '.$e->getMessage());
            }
        }
    }
}
