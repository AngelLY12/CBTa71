<?php

namespace App\Core\Application\UseCases\Jobs;

use App\Core\Application\DTO\Request\PaymentConcept\UpdatePaymentConceptRelationsDTO;
use App\Core\Application\Traits\HasPaymentConcept;
use App\Core\Domain\Entities\PaymentConcept;
use App\Core\Domain\Enum\PaymentConcept\PaymentConceptAppliesTo;
use App\Core\Domain\Repositories\Query\User\UserQueryRepInterface;
use App\Jobs\SendConceptUpdatedRelationsNotificationJob;
use Illuminate\Support\Facades\Log;
use function Symfony\Component\String\b;

class ProcessUpdateConceptRecipientsUseCase
{

    use HasPaymentConcept;

    public function __construct(
        private UserQueryRepInterface $uqRepo,
    )
    {
        $this->setRepository($uqRepo);
    }

    public function execute(PaymentConcept $newPaymentConcept, PaymentConcept $oldPaymentConcept, array $oldRecipientIds ,UpdatePaymentConceptRelationsDTO $dto ,string $appliesTo): void
    {
        $notificationData=$this->getNotificationData($newPaymentConcept,$oldPaymentConcept);
        $notificationDecision= $this->shouldSendNotification($newPaymentConcept, $oldRecipientIds,$notificationData, $dto);
        $recipients=[];

        if (!$notificationDecision['should']) {
            Log::info('No notification needed for concept update', [
                'concept_id' => $newPaymentConcept->id
            ]);
            return;
        }

        if(in_array('email', $notificationDecision['notification_type'])) {
            if(empty($notificationDecision['newUserIds']))
            {
                $recipients = $this->uqRepo->getRecipients($newPaymentConcept, $appliesTo);
            }else
            {
                $recipients=$this->uqRepo->getRecipientsFromIds($notificationDecision['newUserIds']);
            }
            if(empty($recipients)){
                Log::warning('Payment concept created but no recipients found for notifications', [
                    'concept_id' => $newPaymentConcept->id,
                    'applies_to' => $appliesTo
                ]);
                return;
            }
            $this->notifyRecipients($newPaymentConcept,$recipients);

            Log::info('Payment concept update notifications sent', [
                'concept_id' =>$newPaymentConcept->id,
                'reason' => $notificationDecision['reason'],
                'recipient_count' => count($recipients),
                'applies_to' => $newPaymentConcept->applies_to->value
            ]);
        }



        if (in_array('broadcast', $notificationDecision['notification_type']) && !empty($notificationDecision['newUserIds'])) {
            $this->sendBroadcastForNewUserIds($newPaymentConcept, $notificationDecision);
            Log::info('Payment concept broadcast notifications sent', [
                'concept_id' => $newPaymentConcept->id,
                'reason' => $notificationDecision['reason'],
                'recipient_count' => count($notificationDecision['newUserIds']),
                'applies_to' => $newPaymentConcept->applies_to->value
            ]);
        }
        if(in_array('broadcast', $notificationDecision['notification_type']) && empty($notificationDecision['newUserIds']))
        {
            $userIds=[];
            foreach ($recipients as $recipient)
            {
                $userIds[]=$recipient->id;
            }
            $this->sendBroadcasteForAppliesChanged($newPaymentConcept, $oldPaymentConcept,$userIds, $notificationDecision);
        }
    }

    private function sendBroadcasteForAppliesChanged(PaymentConcept $newConcept, PaymentConcept $oldConcept ,array $userIds, array $notificationDecision): void
    {
        $changes=[];
        switch ($notificationDecision['reason'])
        {
            case 'applies_to_changed':
                $changes = [
                    [
                        'field' =>'applies_to',
                        'type' => 'applies_to_changed',
                        'old' => $oldConcept->applies_to->value,
                        'new' => $newConcept->applies_to->value,
                    ]
                ];
                break;
        }
        if($newConcept->is_global !== $oldConcept->is_global)
        {
            $changes = [
                [
                    'field' =>'is_global',
                    'type' => 'applies_to_changed',
                    'old' => $oldConcept->is_global,
                    'new' => $newConcept->is_global,
                ]
            ];
        }
        SendConceptUpdatedRelationsNotificationJob::forStudents(
            $userIds,
            $newConcept->id,
            $changes
        )
            ->onQueue('default')
            ->delay(now()->addSeconds(5));
    }

    private function sendBroadcastForNewUserIds(PaymentConcept $concept, array $notificationDecision): void
    {
        $changes = [];
        switch ($notificationDecision['reason'])
        {
            case 'exceptions_removed':
                $changes = [
                    [
                        'type' => 'exceptions_update',
                        'field' => 'exceptions',
                        'added' => [],
                        'removed' => $notificationDecision['newUserIds']
                    ]
                ];
                break;
            case 'exceptions_added':
                $changes = [
                    [
                        'type' => 'exceptions_update',
                        'field' => 'exceptions',
                        'added' => $notificationDecision['newUserIds'],
                        'removed' => []
                    ]
                ];
                break;
            case 'careers_updated':
                $changes = [
                    [
                        'type' => 'relation_update',
                        'field' => 'careers',
                        'added' => $notificationDecision['newUserIds'],
                        'removed' => []
                    ]
                ];
                break;
            case 'semesters_updated':
                $changes = [
                    [
                        'type' => 'relation_update',
                        'field' => 'semesters',
                        'added' => $notificationDecision['newUserIds'],
                        'removed' => []
                    ]
                ];
                break;
            case 'students_updated':
                $changes = [
                    [
                        'type' => 'relation_update',
                        'field' => 'students',
                        'added' => $notificationDecision['newUserIds'],
                        'removed' => []
                    ]
                ];
                break;
            case 'tags_updated':
                $changes = [
                    [
                        'type' => 'relation_update',
                        'field' => 'applicant_tags',
                        'added' => $notificationDecision['newUserIds'],
                        'removed' => []
                    ]
                ];
                break;
        }
        SendConceptUpdatedRelationsNotificationJob::forStudents(
            $notificationDecision['newUserIds'],
            $concept->id,
            $changes
        )
            ->onQueue('default')
            ->delay(now()->addSeconds(5));
    }
    private function shouldSendNotification(PaymentConcept $newPaymentConcept,array $oldRecipientIds,array $notificationData, UpdatePaymentConceptRelationsDTO $dto): array
    {
        $oldAppliesTo = $notificationData['old_applies_to'];
        $newAppliesTo = $notificationData['new_applies_to'];

        if ($dto->appliesTo && $oldAppliesTo !== $newAppliesTo) {
            return [
                'should' => true,
                'newUserIds' => [],
                'reason' => 'applies_to_changed',
                'notification_type' => ['email', 'broadcast'],
                'applies_to' => $newAppliesTo->value
            ];
        }

        if ($dto->removeAllExceptions && !empty($notificationData['old_exception_ids'])) {
            return [
                'should' => true,
                'newUserIds' => $notificationData['old_exception_ids'],
                'reason' => 'exceptions_removed',
                'notification_type' => ['email', 'broadcast'],
                'applies_to' => $newAppliesTo->value
            ];
        }
        if ($dto->exceptionStudents && $dto->replaceExceptions) {
            $oldExceptionIds = $notificationData['old_exception_ids'];
            $newExceptionIds = $notificationData['new_exception_ids'];

            $addedToExceptions = array_diff($newExceptionIds, $oldExceptionIds);

            if (!empty($addedToExceptions)) {
                return [
                    'should' => true,
                    'newUserIds' => $addedToExceptions,
                    'reason' => 'exceptions_added',
                    'notification_type' => ['broadcast'],
                    'applies_to' => $newAppliesTo->value
                ];
            }
        }

        if (!$dto->appliesTo && $oldAppliesTo === $newAppliesTo)
        {
            $newRecipientIds = $this->uqRepo->getRecipientsIds($newPaymentConcept, $newAppliesTo->value);
            $newlyAddedIds = array_diff($newRecipientIds, $oldRecipientIds);

            if (!empty($newlyAddedIds)) {
                return [
                    'should' => true,
                    'newUserIds' => $newlyAddedIds,
                    'reason' => $this->determineRelationChangeReason($oldAppliesTo, $dto),
                    'notification_type' => ['email', 'broadcast'],
                    'applies_to' => $newAppliesTo->value
                ];
            }
        }

        return [
            'should' => false,
            'newUserIds' => [],
            'reason' => '',
            'notification_type' => [],
            'applies_to' => $newAppliesTo->value
        ];
    }

    private function determineRelationChangeReason(PaymentConceptAppliesTo $oldAppliesTo, UpdatePaymentConceptRelationsDTO $dto): string
    {
        if ($oldAppliesTo === PaymentConceptAppliesTo::CARRERA && $dto->careers) {
            return 'careers_updated';
        }

        if ($oldAppliesTo === PaymentConceptAppliesTo::SEMESTRE && $dto->semesters) {
            return 'semesters_updated';
        }

        if ($oldAppliesTo === PaymentConceptAppliesTo::ESTUDIANTES && $dto->students) {
            return 'students_updated';
        }

        if ($oldAppliesTo === PaymentConceptAppliesTo::TAG && $dto->applicantTags) {
            return 'tags_updated';
        }

        if ($oldAppliesTo === PaymentConceptAppliesTo::CARRERA_SEMESTRE && ($dto->careers || $dto->semesters)) {
            return 'career_semester_updated';
        }

        return 'relations_updated';
    }

    private function getNotificationData(PaymentConcept $newPaymentConcept, PaymentConcept $oldPaymentConcept): array
    {
        return [
            'old_applies_to' => $oldPaymentConcept->applies_to,
            'new_applies_to' => $newPaymentConcept->applies_to,
            'old_exception_ids' => $oldPaymentConcept->getExceptionUsersIds(),
            'new_exception_ids' => $newPaymentConcept->getExceptionUsersIds(),
            'old_career_ids' => $oldPaymentConcept->getCareerIds(),
            'new_career_ids' => $newPaymentConcept->getCareerIds(),
            'old_semesters' => $oldPaymentConcept->getSemesters(),
            'new_semesters' => $newPaymentConcept->getSemesters(),
            'old_user_ids' => $oldPaymentConcept->getUserIds(),
            'new_user_ids' => $newPaymentConcept->getUserIds(),
            'old_applicant_tags' => $oldPaymentConcept->getApplicantTag(),
            'new_applicant_tags' => $newPaymentConcept->getApplicantTag(),
        ];
    }
}
