<?php

namespace App\Core\Application\UseCases\Admin;

use App\Core\Application\DTO\Response\General\ImportResponse;
use App\Core\Domain\Repositories\Command\User\StudentDetailReInterface;
use App\Core\Domain\Repositories\Query\User\UserQueryRepInterface;
use App\Jobs\ClearStaffCacheJob;

class BulkImportStudentDetailsUseCase
{
    private const CHUNK_SIZE = 200;
    private const USER_BATCH_SIZE = 1000;
    public function __construct(
        private StudentDetailReInterface $sdRepo,
        private UserQueryRepInterface $userRepo
    ) {}

    public function execute(array $rows): ImportResponse
    {
        $result = new ImportResponse();
        $result->setTotalRows(count($rows));

        foreach (array_chunk($rows, self::CHUNK_SIZE) as $chunkIndex => $chunk) {
            try {
                $chunkResult = $this->processChunk($chunk, $chunkIndex);
                $result->merge($chunkResult);
            } catch (\Throwable $e) {
                $result->addGlobalError(
                    "Error procesando chunk {$chunkIndex}: " . $e->getMessage(),
                    $chunkIndex,
                    count($chunk)
                );
                logger()->error('Error importing student details: '.$e->getMessage(), [
                    'chunk_index' => $chunkIndex,
                    'chunk_size' => count($chunk),
                    'exception' => $e
                ]);
                continue;
            }
        }
        $this->dispatchCacheClear();

        return $result;

    }

    private function processChunk(array $rows, int $chunkIndex): ImportResponse
    {
        $result = new ImportResponse();
        $curps = $this->extractValidCurps($rows);

        if (empty($curps)) {
            $result->addWarning(
                "Chunk {$chunkIndex} sin CURPs válidas",
                $chunkIndex,
                count($rows)
            );
            return $result;
        }
        $userMap = $this->buildUserMap($curps);
        $studentDetailsToInsert = $this->prepareStudentDetails($rows, $userMap, $result, $chunkIndex);

        if (empty($studentDetailsToInsert)) {
            $result->addWarning(
                "Chunk {$chunkIndex} sin registros válidos para insertar",
                $chunkIndex,
                count($rows)
            );
            return $result;
        }

        try {
            $cleanData = $this->cleanForBatchInsert($studentDetailsToInsert);
            $inserted = $this->sdRepo->insertStudentDetails($cleanData);
            $result->incrementInserted($inserted);
        } catch (\Exception $e) {
            if ($this->isDuplicateError($e)) {
                $individualResult = $this->insertIndividually($studentDetailsToInsert);
                $result->incrementInserted($individualResult['inserted']);

                foreach ($individualResult['errors'] as $error) {
                    $result->addError($error['message'], $error['row_number'], $error['context']);
                }
            } else {
                $result->addGlobalError(
                    "Error insertando chunk {$chunkIndex}: " . $e->getMessage(),
                    $chunkIndex,
                    count($studentDetailsToInsert)
                );
            }
        }

        return $result;
    }

    private function insertIndividually(array $studentDetails): array
    {
        $inserted = 0;
        $errors = [];

        foreach ($studentDetails as $detail) {
            try {
                $rowNumber = $detail['_original_row_number'];
                unset($detail['_original_row_number']);

                $this->sdRepo->insertSingleStudentDetail($detail);
                $inserted++;

            } catch (\Exception $e) {
                if ($this->isDuplicateError($e)) {
                    $errors[] = [
                        'message' => 'Registro duplicado',
                        'row_number' => $rowNumber,
                        'context' => [
                            'user_id' => $detail['user_id'],
                            'n_control' => $detail['n_control']
                        ]
                    ];
                }
            }
        }

        return [
            'inserted' => $inserted,
            'errors' => $errors
        ];
    }

    private function extractValidCurps(array $rows): array
    {
        $curps = [];

        foreach ($rows as $row) {
            if (!empty($row['curp'])) {
                $curps[] = $row['curp'];
            }
        }

        return array_unique($curps);
    }

    private function cleanForBatchInsert(array $studentDetails): array
    {
        return array_map(function($detail) {
            unset($detail['_original_row_number']);
            return $detail;
        }, $studentDetails);
    }
    private function isDuplicateError(\Exception $e): bool
    {
        $message = $e->getMessage();

        return str_contains($message, '1062') ||
            str_contains($message, 'Duplicate entry') ||
            str_contains($message, 'Integrity constraint violation') ||
            (str_contains($message, 'SQLSTATE[23000]') && str_contains($message, '1062'));
    }

    private function buildUserMap(array $curps): array
    {
        $userMap = [];

        if (count($curps) > self::USER_BATCH_SIZE) {
            foreach (array_chunk($curps, self::USER_BATCH_SIZE) as $curpChunk) {
                $userMap = array_merge(
                    $userMap,
                    $this->fetchUsersByCurps($curpChunk)
                );
            }
        } else {
            $userMap = $this->fetchUsersByCurps($curps);
        }

        return $userMap;
    }

    private function fetchUsersByCurps(array $curps): array
    {
        $userMap = [];
        $usersGenerator = $this->userRepo->getUsersByCurpCursor($curps);

        foreach ($usersGenerator as $user) {
            $userMap[$user->curp] = $user;
        }

        return $userMap;
    }

    private function prepareStudentDetails(
        array $rows,
        array $userMap,
        ImportResponse $result,
        int $chunkIndex
    ): array
    {
        $studentDetailsToInsert = [];
        $now = now();

        foreach ($rows as $index => $row) {
            $rowNumber = ($chunkIndex * self::CHUNK_SIZE) + $index + 1;

            if (!$this->isValidRow($row, $userMap, $result, $rowNumber)) {
                continue;
            }

            $user = $userMap[$row['curp']];

            $studentDetailsToInsert[] = [
                'user_id' => $user->id,
                'career_id' => $row['career_id'],
                'n_control' => $row['n_control'],
                'semestre' => $row['semestre'],
                'group' => isset($row['group']) ? trim($row['group']) : null,
                'workshop' => isset($row['workshop']) ? trim($row['workshop']) : null,
                'created_at' => $now,
                'updated_at' => $now,
                '_original_row_number' => $rowNumber,
            ];

            $result->incrementProcessed();
        }

        return $studentDetailsToInsert;
    }

    private function isValidRow(
        array $row,
        array $userMap,
        ImportResponse $result,
        int $rowNumber
    ): bool
    {
        $errors = [];

        if (empty($row['curp'])) {
            $errors[] = 'CURP requerida';
        } elseif (!isset($userMap[$row['curp']])) {
            $errors[] = 'CURP no encontrada en el sistema';
        }

        if (empty($row['career_id'])) {
            $errors[] = 'career_id requerido';
        }

        if (empty($row['n_control'])) {
            $errors[] = 'n_control requerido';
        }

        if (empty($row['semestre'])) {
            $errors[] = 'semestre requerido';
        }

        if (!empty($errors)) {
            $result->addError(
                implode(', ', $errors),
                $rowNumber,
                ['curp' => $row['curp'] ?? 'N/A']
            );
            return false;
        }

        return true;
    }

    private function dispatchCacheClear(): void
    {
        ClearStaffCacheJob::dispatch()
            ->delay(now()->addSeconds(rand(5, 15)));
    }
}
