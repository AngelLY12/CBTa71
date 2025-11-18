<?php

namespace App\Core\Application\UseCases\Admin;

use App\Core\Application\Mappers\MailMapper;
use App\Core\Domain\Repositories\Command\UserRepInterface;
use App\Jobs\SendMailJob;
use App\Mail\CreatedUserMail;

class BulkImportUsersUseCase
{
    public function __construct(
        private UserRepInterface $userRepo
    ) {}

    public function execute(array $rows): int
    {
        $chunks = array_chunk($rows, 200);
        $affected=[];
        foreach ($chunks as $chunk) {
            try {
                $affected=$this->userRepo->bulkInsertWithStudentDetails($chunk);
                $this->notifyRecipients($affected);
            } catch (\Throwable $e) {
                logger()->error('Error importing users: '.$e->getMessage());
            }
        }
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

            SendMailJob::dispatch(
                new CreatedUserMail(
                    MailMapper::toNewUserCreatedEmailDTO($dtoData)
                ),
                $user->email
            )->delay(now()->addSeconds(rand(1, 5)));
        }
    }

}
