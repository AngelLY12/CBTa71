<?php

namespace App\Core\Application\UseCases\Admin;

use App\Core\Application\DTO\Response\General\ImportResponse;
use App\Core\Application\Mappers\EnumMapper;
use App\Core\Application\Mappers\MailMapper;
use App\Core\Domain\Enum\User\UserRoles;
use App\Core\Domain\Enum\User\UserStatus;
use App\Core\Domain\Repositories\Command\Auth\RolesAndPermissionsRepInterface;
use App\Core\Domain\Repositories\Command\User\StudentDetailReInterface;
use App\Core\Domain\Repositories\Command\User\UserRepInterface;
use App\Core\Domain\Repositories\Query\Auth\RolesAndPermissosQueryRepInterface;
use App\Core\Domain\Repositories\Query\Misc\CareerQueryRepInterface;
use App\Core\Infraestructure\Mappers\UserMapper;
use App\Jobs\ClearStaffCacheJob;
use App\Jobs\SendBulkMailJob;
use App\Mail\CreatedUserMail;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class BulkImportUsersUseCase
{
    private const CHUNK_SIZE = 200;
    private ImportResponse $importResponse;
    private array $cachedCareerIds =[];


    public function __construct(
        private UserRepInterface $userRepo,
        private RolesAndPermissionsRepInterface $rpRepo,
        private StudentDetailReInterface $sdRepo,
        private RolesAndPermissosQueryRepInterface $rpqRepo,
        private CareerQueryRepInterface $cqRepo,
    ) {
    }
    public function execute(array $rows): ImportResponse
    {
        $this->importResponse = new ImportResponse();
        $this->importResponse->setTotalRows(count($rows));
        $allUsersToNotify = [];
        $this->loadCareerIds();
        foreach (array_chunk($rows, self::CHUNK_SIZE) as $chunkIndex => $chunk) {
            try {
                $chunkResult = $this->processChunk($chunk, $chunkIndex);
                $allUsersToNotify = array_merge($allUsersToNotify, $chunkResult['usersToNotify']);
            } catch (\Throwable $e) {
                $this->importResponse->addGlobalError(
                    "Error procesando chunk {$chunkIndex}: " . $e->getMessage(),
                    $chunkIndex,
                    count($chunk)
                );
                logger()->error('Error importing users chunk', [
                    'chunk_index' => $chunkIndex,
                    'chunk_size' => count($chunk),
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
            }
        }

        $this->processNotifications($allUsersToNotify);

        $this->dispatchCacheClear();

        return $this->importResponse;
    }

    private function loadCareerIds(): void
    {
        if (empty($this->cachedCareerIds)) {
            $this->cachedCareerIds = $this->cqRepo->findAllIds();
        }
    }

    private function processChunk(array $rows, int $chunkIndex): array
    {
        $roles = $this->loadRoles();

        return DB::transaction(function () use ($rows, $roles, $chunkIndex) {
            $usersData = [];
            $tempPasswords = [];
            $validRows = [];

            foreach ($rows as $rowIndex => $row) {
                $rowNumber = ($chunkIndex * self::CHUNK_SIZE) + $rowIndex + 1;

                if (!$this->isValidRow($row, $rowNumber)) {
                    continue;
                }
                $tempPassword = Str::random(12);
                $tempPasswords[] = $tempPassword;

                $userData = $this->prepareUserData($row, $tempPassword, $rowNumber);

                $usersData[] = $userData;

                $validRows[] = $row;
                $this->importResponse->incrementProcessed();

            }

            if (empty($usersData)) {
                $this->importResponse->addWarning(
                    "Chunk {$chunkIndex} sin datos válidos para insertar",
                    $chunkIndex,
                    count($rows)
                );
                return [
                    'usersToNotify' => []
                ];
            }

            try {
                $cleanData = $this->cleanForBatchInsert($usersData);
                $insertedUsers = $this->userRepo->insertManyUsers($cleanData);
                $this->importResponse->incrementInserted($insertedUsers->count());

            } catch (\Exception $e) {
                if ($this->isDuplicateError($e)) {
                    $individualResult = $this->insertUsersIndividually($usersData, $validRows, $tempPasswords, $chunkIndex);

                    $this->importResponse->incrementInserted($individualResult['inserted']);

                    $processingResult = $this->processInsertedUsers(
                        $individualResult['insertedUsers'],
                        $individualResult['validRows'],
                        $individualResult['tempPasswords'],
                        $roles,
                        $chunkIndex
                    );

                    foreach ($individualResult['errors'] as $error) {
                        $this->importResponse->addError($error['message'], $error['row_number'], $error['context']);
                    }

                    app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

                    return [
                        'usersToNotify' => $processingResult['usersToNotify']
                    ];

                } else {
                    throw $e;
                }
            }

            $processingResult = $this->processInsertedUsers($insertedUsers, $validRows, $tempPasswords, $roles, $chunkIndex);

            app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

            return [
                'usersToNotify' => $processingResult['usersToNotify']
            ];
        });
    }

    private function isValidRow(array $row, int $rowNumber): bool
    {
        $errors = [];

        if (empty($row[0]) || trim($row[0]) === '') {
            $errors[] = 'Nombre requerido';
        }

        if (empty($row[1]) || trim($row[1]) === '') {
            $errors[] = 'Apellido requerido';
        }

        if (empty($row[2]) || trim($row[2]) === '') {
            $errors[] = 'Email requerido';
        } else{
            $email = trim($row[2]);
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = 'Email inválido';
            }
        }
        if(empty($row[3]) || trim($row[3]) === ''){
            $errors[] = 'Número de telefono requerido';
        }else
        {
            $phone = trim($row[3]);

            if (!preg_match('/^\+52\d{10}$/', $phone)) {
                $normalized = $this->normalizePhoneNumber($phone);
                if ($normalized && preg_match('/^\+52\d{10}$/', $normalized)) {
                    logger()->info('Teléfono normalizado durante importación', [
                        'original' => $phone,
                        'normalized' => $normalized,
                        'row' => $rowNumber
                    ]);
                } else {
                    $errors[] = 'Teléfono debe tener formato exacto: +52 seguido de 10 dígitos (ej: +521234567890)';
                }
            }
        }

        if (empty($row[6]) || trim($row[6]) === '') {
            $errors[] = 'CURP requerida';
        }

        if (!empty($errors)) {
            $this->importResponse->addError(
                implode(', ', $errors),
                $rowNumber,
                [
                    'email' => $row[2] ?? 'N/A',
                    'curp' => $row[6] ?? 'N/A',
                    'phone' => $row[3] ?? 'N/A'
                ]
            );
            return false;
        }

        return true;
    }

    private function loadRoles(): array
    {
        return [
            'unverified' => $this->rpqRepo->findRoleByName(UserRoles::UNVERIFIED->value),
            'student' => $this->rpqRepo->findRoleByName(UserRoles::STUDENT->value)
        ];
    }

    private function processInsertedUsers(
        Collection $insertedUsers,
        array $rows,
        array $tempPasswords,
        array $roles,
        int $chunkIndex
    ): array {
        $studentDetails = [];
        $roleRows = [];
        $usersToNotify = [];
        $invalidStudentDetailsCount = 0;
        $invalidSemesterCount = 0;
        foreach ($insertedUsers as $index => $user) {
            $row = $rows[$index];
            $tempPassword = $tempPasswords[$index];
            $rowNumber = ($chunkIndex * self::CHUNK_SIZE) + $index + 1;
            $hasStudentDetails = $this->hasStudentDetails($row);
            $hasStudentFields = !empty($row[14]) || !empty($row[15]) || !empty($row[16]);
            if ($hasStudentFields && !$hasStudentDetails) {
                $invalidStudentDetailsCount++;
                $semestre = isset($row[16]) ? (int) trim($row[16]) : 0;
                if ($semestre < 1 || $semestre > 10) {
                    $invalidSemesterCount++;
                }
            }

            $roleId = $hasStudentDetails ? $roles['student']->id : $roles['unverified']->id;
            $roleRows[] = $this->prepareRole($roleId, $user);

            if ($hasStudentDetails) {
                $studentDetail = $this->prepareStudentDetails($user, $row);
                if ($studentDetail) {
                    $studentDetails[] = $studentDetail;
                    $this->rpRepo->givePermissionsByType($user, UserRoles::STUDENT->value);
                }else {
                    $invalidStudentDetailsCount++;
                }
            }

            $usersToNotify[] = [
                'user' => UserMapper::toDomain($user),
                'password' => $tempPassword,
            ];
        }
        if ($invalidStudentDetailsCount > 0) {
            $warningMessage = "{$invalidStudentDetailsCount} usuario(s) tenían campos de estudiante inválidos";

            if ($invalidSemesterCount > 0) {
                $warningMessage .= " ({$invalidSemesterCount} con semestre fuera de rango 1-10)";
            }

            $warningMessage .= ". Se asignó rol UNVERIFIED.";

            $this->importResponse->addWarning(
                $warningMessage,
                $chunkIndex,
                count($rows)
            );
        }
        if (!empty($studentDetails)) {
            $this->sdRepo->insertStudentDetails($studentDetails);
        }

        if (!empty($roleRows)) {
            $this->rpRepo->assignRoles($roleRows);
        }

        return [
            'usersToNotify' => $usersToNotify,
        ];
    }

    private function insertUsersIndividually(
        array $usersData,
        array $validRows,
        array $tempPasswords,
        int $chunkIndex
    ): array {
        $insertedUsers = [];
        $filteredRows = [];
        $filteredPasswords = [];
        $errors = [];

        foreach ($usersData as $index => $userData) {
            try {
                $rowNumber = $userData['_original_row_number'] ?? 0;
                $dataToInsert = $userData;
                unset($dataToInsert['_original_row_number']);

                $user = $this->userRepo->insertSingleUser($dataToInsert);

                $insertedUsers[] = $user;
                $filteredRows[] = $validRows[$index];
                $filteredPasswords[] = $tempPasswords[$index];

            } catch (\Exception $e) {
                if ($this->isDuplicateError($e)) {
                    $errors[] = [
                        'message' => 'Usuario duplicado',
                        'row_number' => $rowNumber,
                        'context' => [
                            'email' => $userData['email'] ?? 'N/A',
                            'curp' => $userData['curp'] ?? 'N/A'
                        ]
                    ];
                } else {
                    throw $e;
                }
            }
        }

        return [
            'inserted' => count($insertedUsers),
            'insertedUsers' => collect($insertedUsers),
            'validRows' => $filteredRows,
            'tempPasswords' => $filteredPasswords,
            'errors' => $errors
        ];
    }

    private function cleanForBatchInsert(array $usersData): array
    {
        return array_map(function($userData) {
            unset($userData['_original_row_number']);
            return $userData;
        }, $usersData);
    }

    private function isDuplicateError(\Exception $e): bool
    {
        $message = $e->getMessage();

        return str_contains($message, '1062') ||
            str_contains($message, 'Duplicate entry') ||
            str_contains($message, 'Integrity constraint violation') ||
            (str_contains($message, 'SQLSTATE[23000]') && str_contains($message, '1062'));
    }

    private function hasStudentDetails(array $row): bool
    {
        $careerId = isset($row[14]) ? trim($row[14]) : '';
        $nControl = isset($row[15]) ? trim($row[15]) : '';
        $semestre = isset($row[16]) ? trim($row[16]) : '';

        if (empty($careerId) || empty($nControl) || empty($semestre)) {
            return false;
        }
        if (!is_numeric($careerId) || !is_numeric($semestre)) {
            return false;
        }
        $semestreValue = (int) $semestre;
        $maxSemester=config('promotions.max_semester');

        if ($semestreValue < 1 || $semestreValue > $maxSemester) {
            return false;
        }
        $careerId = (int) $careerId;
        return in_array($careerId, $this->cachedCareerIds, true);
    }

    private function prepareUserData(array $row, string $tempPassword, int $rowNumber): array
    {
        $trimmedRow = array_map(function($value) {
            return is_string($value) ? trim($value) : $value;
        }, $row);

        $addressData = [
            'street' => isset($trimmedRow[7]) ? $trimmedRow[7] : null,
            'city' => isset($trimmedRow[8]) ? $trimmedRow[8] : null,
            'state' => isset($trimmedRow[9]) ? $trimmedRow[9] : null,
            'zip_code' => isset($trimmedRow[10]) ? $trimmedRow[10] : null,
        ];

        $filteredAddress = array_filter($addressData, fn($value) => !is_null($value));
        $addressJson = !empty($filteredAddress) ? json_encode($addressData, JSON_UNESCAPED_UNICODE) : null;

        $phone = $trimmedRow[3];
        $normalizedPhone = $this->normalizePhoneNumber($phone);

        return [
            'name' => $trimmedRow[0],
            'last_name' => $trimmedRow[1],
            'email' => $trimmedRow[2],
            'password' => Hash::make($tempPassword),
            'phone_number' => $normalizedPhone, // ← Normalizado
            'birthdate' => !empty($trimmedRow[4]) ? Carbon::parse($trimmedRow[4]) : null,
            'gender' => !empty($trimmedRow[5]) ? EnumMapper::toUserGender($trimmedRow[5])->value : null,
            'curp' => $trimmedRow[6],
            'address' => $addressJson,
            'stripe_customer_id' => null,
            'blood_type' => !empty($trimmedRow[11]) ? EnumMapper::toUserBloodType($trimmedRow[11])->value : null,
            'registration_date' => !empty($trimmedRow[12]) ? Carbon::parse($trimmedRow[12]) : now(),
            'status' => !empty($trimmedRow[13]) ? EnumMapper::toUserStatus($trimmedRow[13])->value : UserStatus::ACTIVO->value,
            'created_at' => now(),
            'updated_at' => now(),
            'email_verified_at' => null,
            '_original_row_number' => $rowNumber,
        ];
    }

    private function prepareStudentDetails($user, array $row): ?array
    {
        $careerId = (int) trim($row[14]);

        if (!in_array($careerId, $this->cachedCareerIds, true)) {
            return null;
        }

        $semestre = (int) $row[16];
        $maxSemester=config('promotions.max_semester');
        if ($semestre < 1 || $semestre > $maxSemester) {
            return null;
        }

        return [
            'user_id' => $user->id,
            'career_id' => $careerId,
            'n_control' => trim($row[15]),
            'semestre' => $semestre,
            'group' => isset($row[17]) ? trim($row[17]) : null,
            'workshop' => isset($row[18]) ? trim($row[18]) : null,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    private function prepareRole($roleId, $user): array
    {
        return [
            'role_id' => $roleId,
            'model_type' => User::class,
            'model_id' => $user->id,
        ];
    }

    private function processNotifications(array $usersToNotify): void
    {
        if (empty($usersToNotify)) {
            return;
        }
        $chunks = array_chunk($usersToNotify, 100);

        foreach ($chunks as $chunkIndex => $chunk) {
            $mailables = [];
            $recipientEmails = [];

            foreach ($chunk as $data) {
                $user = $data['user'];
                $password = $data['password'];

                $dtoData = [
                    'recipientName'  => $user->name . ' ' . $user->last_name,
                    'recipientEmail' => $user->email,
                    'password'       => $password
                ];

                $mailables[] = new CreatedUserMail(
                    MailMapper::toNewUserCreatedEmailDTO($dtoData)
                );
                $recipientEmails[] = $user->email;
            }

            SendBulkMailJob::forRecipients(
                $mailables,
                $recipientEmails,
                'bulk_import_user_registration'
            )
                ->onQueue('emails')
                ->delay(now()->addSeconds(5));
        }
    }

    private function normalizePhoneNumber(string $phoneNumber): ?string
    {
        $phone = trim($phoneNumber);

        $clean = preg_replace('/[\s\-\.\(\)]/', '', $phone);

        if (preg_match('/^\+52\d{10}$/', $phone)) {
            return $phone;
        }

        if (preg_match('/^52\d{10}$/', $clean)) {
            return '+' . $clean;
        }

        if (preg_match('/^\d{10}$/', $clean)) {
            return '+52' . $clean;
        }

        if (preg_match('/^\+\s*\d{10}$/', $phone)) {
            $digits = preg_replace('/^\+\s*/', '', $phone);
            $digits = preg_replace('/\D/', '', $digits);

            if (strlen($digits) === 10) {
                return '+52' . $digits;
            }
        }

        if (preg_match('/^\+\s*52/', $phone)) {
            $withoutPlus = preg_replace('/^\+\s*/', '', $phone);
            $digitsOnly = preg_replace('/\D/', '', $withoutPlus);

            if (strlen($digitsOnly) === 10) {
                return '+52' . $digitsOnly;
            }
        }

        $digitsOnly = preg_replace('/\D/', '', $phone);
        if (strlen($digitsOnly) === 10) {
            return '+52' . $digitsOnly;
        }

        return null;

    }

    private function dispatchCacheClear(): void
    {
        ClearStaffCacheJob::dispatch()
            ->onQueue('cache');
    }

}
