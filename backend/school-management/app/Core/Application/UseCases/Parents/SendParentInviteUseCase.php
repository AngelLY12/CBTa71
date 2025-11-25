<?php

namespace App\Core\Application\UseCases\Parents;

use App\Core\Application\Mappers\MailMapper;
use App\Core\Application\Mappers\ParentInviteMapper;
use App\Core\Domain\Entities\ParentInvite;
use App\Core\Domain\Entities\User;
use App\Core\Domain\Repositories\Command\ParentInviteRepInterface;
use App\Core\Domain\Repositories\Query\ParentStudentQueryRepInterface;
use App\Core\Domain\Repositories\Query\UserQueryRepInterface;
use App\Exceptions\Conflict\RelationAlreadyExistsException;
use App\Exceptions\NotFound\UserNotFoundException;
use App\Jobs\SendMailJob;
use App\Mail\SendParentInviteEmail;
use Illuminate\Validation\ValidationException;

class SendParentInviteUseCase
{
    public function __construct(
        private ParentInviteRepInterface $inviteRepo,
        private ParentStudentQueryRepInterface $relationQRepo,
        private UserQueryRepInterface $userQRepo,
    ) {}

    public function execute(int $studentId, string $parentEmail, int $createdBy): ParentInvite
    {
        $student=$this->userQRepo->findById($studentId);
        $parent=$this->userQRepo->findUserByEmail($parentEmail);
        if($student->email === $parentEmail)
            throw new ValidationException("No puedes invitarte a ti mismo");

        if(!$student || !$parent)
        {
            throw new UserNotFoundException();
        }
        if ($this->relationQRepo->exists($parent->id, $studentId)) {
            throw new RelationAlreadyExistsException();
        }
        $invite= ParentInviteMapper::toDomain(
            [
                'studentId'=>$student->id,
                'parentEmail' => $parentEmail,
                'createdBy' => $createdBy
            ]
        );
        $invite = $this->inviteRepo->create($invite);
        $this->notifyRecipients($parent, $invite->token);
        return $invite;
    }

    private function notifyRecipients(User $user, string $token): void {
            $dtoData = [
                'recipientName'  => $user->fullName(),
                'recipientEmail' => $user->email,
                'token'       => $token
            ];

            SendMailJob::dispatch(
                new SendParentInviteEmail(
                    MailMapper::toSendParentInviteEmail($dtoData)
                ),
                $user->email
            )->delay(now()->addSeconds(rand(1, 5)));

    }
}
