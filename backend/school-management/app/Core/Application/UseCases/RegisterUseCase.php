<?php

namespace App\Core\Application\UseCases;

use App\Core\Application\DTO\Request\User\CreateUserDTO;
use App\Core\Application\Mappers\MailMapper;
use App\Core\Domain\Entities\User;
use App\Core\Domain\Repositories\Command\UserRepInterface;
use App\Core\Domain\Utils\Validators\UserValidator;
use App\Jobs\SendMailJob;
use App\Mail\CreatedUserMail;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\DB;

class RegisterUseCase
{
    public function __construct(
        private UserRepInterface $userRepo
    )
    {}

    public function execute(CreateUserDTO $create, ?string $password= null): User
    {
        $user= DB::transaction(function () use ($create) {
            $user= $this->userRepo->create($create);
            $role= $this->userRepo->assignRole($user->id, 'unverified');
            if(!$role){ throw new \RuntimeException("Hubo un fallo al agregar el rol al usuario {$user->id}");}
            return $user;
        });

        if($password)
        {
            $this->notifyRecipients($user, $password);
        }
        event(new Registered($user));
        return $user;
    }

    private function notifyRecipients(User $user, $password): void {
            $dtoData = [
                'recipientName'  => $user->fullName(),
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
