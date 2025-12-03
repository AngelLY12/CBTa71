<?php

namespace App\Core\Application\Traits;

use App\Core\Application\DTO\Request\PaymentConcept\CreatePaymentConceptDTO;
use App\Core\Application\DTO\Request\PaymentConcept\UpdatePaymentConceptDTO;
use App\Core\Application\DTO\Response\User\UserIdListDTO;
use App\Core\Application\Mappers\MailMapper;
use App\Core\Domain\Entities\PaymentConcept;
use App\Core\Domain\Repositories\Query\User\UserQueryRepInterface;
use App\Core\Infraestructure\Cache\CacheService;
use App\Exceptions\NotFound\ExceptionStudentsNotFoundException;
use App\Exceptions\NotFound\StudentsNotFoundException;
use App\Jobs\ClearCacheWhileStatusChangeJob;
use App\Jobs\SendMailJob;
use App\Mail\NewConceptMail;

trait HasPaymentConcept
{

    private UserQueryRepInterface $repository;

    public function setRepository(UserQueryRepInterface $repository): void
    {
        $this->repository = $repository;
    }

    public function getUserIdListDTO(CreatePaymentConceptDTO|UpdatePaymentConceptDTO $dto, bool $exceptions=false): UserIdListDTO
    {
        $list = $exceptions
            ? (array) ($dto->exceptionStudents ?? [])
            : (array) ($dto->students ?? []);

        $userIdListDTO = $this->repository->getUserIdsByControlNumbers($list);

        if ($exceptions && empty($userIdListDTO->userIds)) {
            throw new ExceptionStudentsNotFoundException();
        }

        if (!$exceptions && empty($userIdListDTO->userIds)) {
            throw new StudentsNotFoundException();
        }
        return $userIdListDTO;
    }

    public function notifyRecipients(PaymentConcept $concept, array $recipients): void {
        foreach($recipients as $user) {
            ClearCacheWhileStatusChangeJob::dispatch($user->id, $concept->status)->delay(now()->addSeconds(rand(1, 10)));
            $data = [
                'recipientName'=>$user->name,
                'recipientEmail' => $user->email,
                'concept_name' => $concept->concept_name,
                'amount' => $concept->amount,
                'end_date' => $concept->end_date
            ];
            SendMailJob::dispatch(new NewConceptMail(MailMapper::toNewPaymentConceptEmailDTO($data)), $user->email)->delay(now()->addSeconds(rand(1, 5)));
        }
    }
}
