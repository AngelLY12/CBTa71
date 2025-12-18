<?php

namespace App\Core\Application\UseCases\Admin;

use App\Core\Domain\Repositories\Command\User\StudentDetailReInterface;
use App\Core\Domain\Repositories\Query\User\UserQueryRepInterface;
use App\Jobs\ClearStaffCacheJob;

class BulkImportStudentDetailsUseCase
{

    public function __construct(
        private StudentDetailReInterface $sdRepo,
        private UserQueryRepInterface $userRepo
    ) {}

    public function execute(array $rows):int
    {
        $chunks = array_chunk($rows, 200);
        $totalInserted = 0;

        foreach ($chunks as $chunk) {
            try {
                $totalInserted += $this->createStudentDetails($chunk);
            } catch (\Throwable $e) {
                logger()->error('Error importing student details: '.$e->getMessage());
            }
        }

        return $totalInserted;

    }

    private function createStudentDetails(array $rows): int
    {
        $studentDetailsToInsert = [];

        $curps = array_filter(array_column($rows, 'curp'));
        $users = $this->userRepo->getUsersByCurp($curps)
            ->keyBy('curp');

        foreach ($rows as $row) {
            if (empty($row['curp']) || !isset($users[$row['curp']])) {
                continue;
            }

            $user = $users[$row['curp']];

            if (empty($row['career_id']) || empty($row['n_control']) || empty($row['semestre'])) {
                continue;
            }

            $studentDetailsToInsert[] = [
                'user_id' => $user->id,
                'career_id' => $row['career_id'],
                'n_control' => $row['n_control'],
                'semestre' => $row['semestre'],
                'group' => $row['group'] ?? null,
                'workshop' => $row['workshop'] ?? null,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        if (!empty($studentDetailsToInsert)) {
            $this->sdRepo->insertStudentDetails($studentDetailsToInsert);
        }
        ClearStaffCacheJob::dispatch()->delay(now()->addSeconds(rand(1, 10)));

        return count($studentDetailsToInsert);
    }
}
