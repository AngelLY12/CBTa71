<?php

namespace App\Core\Application\UseCases\Admin;

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
use App\Jobs\SendMailJob;
use App\Mail\CreatedUserMail;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class BulkImportUsersUseCase
{
    public function __construct(
        private UserRepInterface $userRepo,
        private RolesAndPermissionsRepInterface $rpRepo,
        private StudentDetailReInterface $sdRepo,
        private RolesAndPermissosQueryRepInterface $rpqRepo
    ) {}

    public function execute(array $rows): int
    {
        $chunks = array_chunk($rows, 200);
        $affected=[];
        foreach ($chunks as $chunk) {
            try {
                $affected=$this->createRegisters($chunk);
                $this->notifyRecipients($affected);
            } catch (\Throwable $e) {
                logger()->error('Error importing users: '.$e->getMessage());
            }
        }
        ClearStaffCacheJob::dispatch()->delay(now()->addSeconds(rand(1, 10)));
        return $affected['affected'];
    }

    private function notifyRecipients(array $affected): void {
        foreach ($affected['users'] as $data) {

            $user = $data['user'];
            $password = $data['password'];

            $dtoData = [
                'recipientName'  => $user->name . ' ' . $user->last_name,
                'recipientEmail' => $user->email,
                'password'       => $password
            ];
            event(new Registered($user));

            SendMailJob::dispatch(
                new CreatedUserMail(
                    MailMapper::toNewUserCreatedEmailDTO($dtoData)
                ),
                $user->email
            )->delay(now()->addSeconds(rand(1, 5)));
        }
    }

    private function createRegisters(array $rows): array
    {
        $createdUsers = [];
        $unverifiedRoleId = $this->rpqRepo->findRoleByName(UserRoles::UNVERIFIED->value);
        $studentRoleId = $this->rpqRepo->findRoleByName(UserRoles::STUDENT->value);
        $totalInserted=DB::transaction(function() use ($rows, &$createdUsers, $unverifiedRoleId, $studentRoleId) {
            $usersData = [];
            $studentDetails = [];
            $roleRows = [];
            $tempPasswords = [];
            foreach ($rows as $row) {
                $tempPassword = Str::random(12);
                $tempPasswords[] = $tempPassword;
                $usersData[] = $this->prepareUserData($row, $tempPassword);
            }

            $insertedUsers = $this->userRepo->insertManyUsers($usersData);

            foreach ($insertedUsers as $index => $user) {
                $row = $rows[$index];

                $hasStudentDetails =
                !empty($row[16]) &&
                !empty($row[17]) &&
                !empty($row[18]);

                if($hasStudentDetails)
                {
                    $studentDetails[] = $this->prepareStudentDetails($user, $row);
                    $roleRows[] = $this->prepareRole($studentRoleId, $user);
                    $this->rpRepo->givePermissionsByType($user,UserRoles::STUDENT->value);
                } else {
                    $roleRows[] = $this->prepareRole($unverifiedRoleId, $user);
                }
                $createdUsers[] = [
                    'user' => UserMapper::toDomain($user),
                    'password' => $tempPasswords[$index]
                ];
            }

            if (!empty($studentDetails)) {
                $this->sdRepo->insertStudentDetails($studentDetails);
            }

            if (!empty($roleRows)) {
                $this->rpRepo->assignRoles($roleRows);
            }

            return [
                'affected'=>$insertedUsers->count(),
                'users'=>$createdUsers
            ];

        });
        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
        return $totalInserted;
    }

    private function prepareUserData($row, string $tempPassword): array
    {
        return [
                'name' => $row[0],
                'last_name' => $row[1],
                'email' => $row[2],
                'password' => Hash::make($tempPassword),
                'phone_number' => $row[3],
                'birthdate' => !empty($row[4]) ? Carbon::parse($row[4]) : null,
                'gender' => !empty($row[5]) ? EnumMapper::toUserGender($row[5])->value : null,
                'curp' => $row[6],
                'address' => [
                    'street' => $row[7] ?? null,
                    'city' => $row[8] ?? null,
                    'state' => $row[9] ?? null,
                    'zip_code' => $row[10] ?? null,
                ],
                'stripe_customer_id' => $row[11] ?? null,
                'blood_type' => !empty($row[12]) ?EnumMapper::toUserBloodType($row[12])->value : null,
                'registration_date' => !empty($row[13]) ? Carbon::parse($row[13]) : now(),
                'status' => !empty($row[14]) ? EnumMapper::toUserStatus($row[14])->value : UserStatus::ACTIVO->value,
                'created_at' => now(),
                'updated_at' => now(),
            ];

    }

    private function prepareStudentDetails($user, $row): array
    {
        return [
            'user_id' => $user->id,
            'career_id' => $row[16],
            'n_control' => $row[17],
            'semestre'  => $row[18],
            'group'     => $row[19] ?? null,
            'workshop'  => $row[20] ?? null,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    private function prepareRole($role, $user)
    {
        return [
            'role_id' => $role->id,
            'model_type' => User::class,
            'model_id' => $user->id,
        ];

    }

}
