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
    private const NOTIFICATION_DELAY_MIN = 1;
    private const NOTIFICATION_DELAY_MAX = 5;
    private const CACHE_CLEAR_DELAY_MIN = 5;
    private const CACHE_CLEAR_DELAY_MAX = 15;
    public function __construct(
        private UserRepInterface $userRepo,
        private RolesAndPermissionsRepInterface $rpRepo,
        private StudentDetailReInterface $sdRepo,
        private RolesAndPermissosQueryRepInterface $rpqRepo
    ) {}

    public function execute(array $rows): ImportResponse
    {
        $result = new ImportResponse();
        $result->setTotalRows(count($rows));
        $allUsersToNotify = [];

        foreach (array_chunk($rows, self::CHUNK_SIZE) as $chunkIndex => $chunk) {
            try {
                $chunkResult = $this->processChunk($chunk, $chunkIndex);
                $result->merge($chunkResult['importResult']);
                $allUsersToNotify = array_merge($allUsersToNotify, $chunkResult['usersToNotify']);
            } catch (\Throwable $e) {
                $result->addGlobalError(
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

        return $result;
    }

    private function processChunk(array $rows, int $chunkIndex): array
    {
        $importResult = new ImportResponse();
        $roles = $this->loadRoles();

        return DB::transaction(function () use ($rows, $roles, $chunkIndex, $importResult) {
            $usersData = [];
            $tempPasswords = [];
            $validRows = [];

            foreach ($rows as $rowIndex => $row) {
                $rowNumber = ($chunkIndex * self::CHUNK_SIZE) + $rowIndex + 1;

                if (!$this->isValidRow($row, $importResult, $rowNumber)) {
                    continue;
                }
                $tempPassword = Str::random(12);
                $tempPasswords[] = $tempPassword;

                $userData = $this->prepareUserData($row, $tempPassword, $rowNumber);

                $usersData[] = $userData;

                $validRows[] = $row;
                $importResult->incrementProcessed();

            }

            if (empty($usersData)) {
                $importResult->addWarning(
                    "Chunk {$chunkIndex} sin datos válidos para insertar",
                    $chunkIndex,
                    count($rows)
                );
                return [
                    'importResult' => $importResult,
                    'usersToNotify' => []
                ];
            }

            try {
                $cleanData = $this->cleanForBatchInsert($usersData);
                $insertedUsers = $this->userRepo->insertManyUsers($cleanData);
                $importResult->incrementInserted($insertedUsers->count());

            } catch (\Exception $e) {
                if ($this->isDuplicateError($e)) {
                    $individualResult = $this->insertUsersIndividually($usersData, $validRows, $tempPasswords, $chunkIndex);

                    $importResult->incrementInserted($individualResult['inserted']);

                    $processingResult = $this->processInsertedUsers(
                        $individualResult['insertedUsers'],
                        $individualResult['validRows'],
                        $individualResult['tempPasswords'],
                        $roles
                    );

                    foreach ($individualResult['errors'] as $error) {
                        $importResult->addError($error['message'], $error['row_number'], $error['context']);
                    }

                    app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

                    return [
                        'importResult' => $importResult,
                        'usersToNotify' => $processingResult['usersToNotify']
                    ];

                } else {
                    throw $e;
                }
            }

            $processingResult = $this->processInsertedUsers($insertedUsers, $validRows, $tempPasswords, $roles);

            app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

            return [
                'importResult' => $importResult,
                'usersToNotify' => $processingResult['usersToNotify']
            ];
        });
    }

    private function isValidRow(array $row, ImportResponse $importResult, int $rowNumber): bool
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
        } elseif (!filter_var($row[2], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Email inválido';
        }

        if (empty($row[6]) || trim($row[6]) === '') {
            $errors[] = 'CURP requerida';
        }

        if (!empty($errors)) {
            $importResult->addError(
                implode(', ', $errors),
                $rowNumber,
                [
                    'email' => $row[2] ?? 'N/A',
                    'curp' => $row[6] ?? 'N/A'
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
        array $roles
    ): array {
        $studentDetails = [];
        $roleRows = [];
        $usersToNotify = [];

        foreach ($insertedUsers as $index => $user) {
            $row = $rows[$index];
            $tempPassword = $tempPasswords[$index];

            $hasStudentDetails = $this->hasStudentDetails($row);

            $roleId = $hasStudentDetails ? $roles['student']->id : $roles['unverified']->id;
            $roleRows[] = $this->prepareRole($roleId, $user);

            if ($hasStudentDetails) {
                $studentDetails[] = $this->prepareStudentDetails($user, $row);

                $this->rpRepo->givePermissionsByType($user, UserRoles::STUDENT->value);
            }

            $usersToNotify[] = [
                'user' => UserMapper::toDomain($user),
                'password' => $tempPassword,
            ];
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
        return !empty($row[16]) && !empty($row[17]) && !empty($row[18]);
    }

    private function prepareUserData(array $row, string $tempPassword, int $rowNumber): array
    {

        return [
            'name' => trim($row[0]),
            'last_name' => trim($row[1]),
            'email' => trim($row[2]),
            'password' => Hash::make($tempPassword),
            'phone_number' => isset($row[3]) ? trim($row[3]) : null,
            'birthdate' => !empty($row[4]) ? Carbon::parse(trim($row[4])) : null,
            'gender' => !empty($row[5]) ? EnumMapper::toUserGender(trim($row[5]))->value : null,
            'curp' => trim($row[6]),
            'address' => [
                'street' => isset($row[7]) ? trim($row[7]) : null,
                'city' => isset($row[8]) ? trim($row[8]) : null,
                'state' => isset($row[9]) ? trim($row[9]) : null,
                'zip_code' => isset($row[10]) ? trim($row[10]) : null,
            ],
            'stripe_customer_id' => isset($row[11]) ? trim($row[11]) : null,
            'blood_type' => !empty($row[12]) ? EnumMapper::toUserBloodType(trim($row[12]))->value : null,
            'registration_date' => !empty($row[13]) ? Carbon::parse(trim($row[13])) : now(),
            'status' => !empty($row[14]) ? EnumMapper::toUserStatus(trim($row[14]))->value : UserStatus::ACTIVO->value,
            'created_at' => now(),
            'updated_at' => now(),
            '_original_row_number' => $rowNumber,
        ];
    }

    private function prepareStudentDetails($user, array $row): array
    {
        return [
            'user_id' => $user->id,
            'career_id' => $row[16],
            'n_control' => $row[17],
            'semestre' => $row[18],
            'group' => isset($row[19]) ? trim($row[19]) : null,
            'workshop' => isset($row[20]) ? trim($row[20]) : null,
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

        foreach ($usersToNotify as $data) {
            event(new Registered($data['user']));
        }
        $chunks = array_chunk($usersToNotify, 50);

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

            $delaySeconds = rand(
                    self::NOTIFICATION_DELAY_MIN,
                    self::NOTIFICATION_DELAY_MAX
                ) * ($chunkIndex + 1);

            SendBulkMailJob::forRecipients(
                $mailables,
                $recipientEmails,
                'bulk_import_user_registration'
            )->delay(now()->addSeconds($delaySeconds));
        }
    }

    private function dispatchCacheClear(): void
    {
        ClearStaffCacheJob::dispatch()
            ->delay(now()->addSeconds(
                rand(self::CACHE_CLEAR_DELAY_MIN, self::CACHE_CLEAR_DELAY_MAX)
            ));
    }

}
