<?php

namespace App\Core\Application\UseCases\Jobs;

use App\Core\Application\DTO\Request\PaymentConcept\UpdatePaymentConceptDTO;
use App\Core\Application\Traits\HasPaymentConcept;
use App\Core\Domain\Entities\PaymentConcept;
use App\Core\Domain\Enum\PaymentConcept\PaymentConceptAppliesTo;
use App\Core\Domain\Repositories\Query\User\UserQueryRepInterface;
use Illuminate\Support\Facades\Log;

class ProcessUpdateConceptRecipientsUseCase
{

    use HasPaymentConcept;

    public function __construct(
        private UserQueryRepInterface $uqRepo,
    )
    {
        $this->setRepository($uqRepo);
    }

    public function execute(PaymentConcept $newPaymentConcept, PaymentConcept $oldPaymentConcept, array $oldRecipientIds ,UpdatePaymentConceptDTO $dto ,string $appliesTo): void
    {
        $notificationData=$this->getNotificationData($newPaymentConcept,$oldPaymentConcept);
        $notificationDecision= $this->shouldSendNotification($newPaymentConcept, $oldRecipientIds,$notificationData, $dto);
        $recipients=[];
        if($notificationDecision['should']){
            if(empty($notificationDecision['newUserIds']))
            {
                $recipients = $this->uqRepo->getRecipients($newPaymentConcept, $appliesTo);
            }else
            {
                $recipients=$this->uqRepo->getRecipientsFromIds($notificationDecision['newUserIds']);
            }
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


    private function shouldSendNotification(PaymentConcept $newPaymentConcept,array $oldRecipientIds,array $notificationData, UpdatePaymentConceptDTO $dto): array
    {
        $oldAppliesTo = $notificationData['old_applies_to'];
        $newAppliesTo = $notificationData['new_applies_to'];

        if ($dto->appliesTo && $oldAppliesTo !== $newAppliesTo) {
            return [
                'should' => true,
                'newUserIds' => [],
                'reason' => 'applies_to_changed',
                'applies_to' => $newAppliesTo->value
            ];
        }

        if ($dto->removeAllExceptions && !empty($notificationData['old_exception_ids'])) {
            $newlyAffectedUserIds = $notificationData['old_exception_ids'];
            return [
                'should' => true,
                'newUserIds' => $newlyAffectedUserIds,
                'reason' => 'exceptions_removed',
                'applies_to' => $newAppliesTo->value
            ];
        }
        if ($dto->exceptionStudents && $dto->replaceExceptions) {
            $oldExceptionIds = $notificationData['old_exception_ids'];
            $newExceptionIds = $notificationData['new_exception_ids'];

            $removedFromExceptions = array_diff($oldExceptionIds, $newExceptionIds);

            if (!empty($removedFromExceptions)) {
                return [
                    'should' => true,
                    'newUserIds' => $removedFromExceptions,
                    'reason' => 'exceptions_updated',
                    'applies_to' => $newAppliesTo->value
                ];
            }
        }
        if (!$dto->appliesTo) {
            if (in_array($oldAppliesTo, [PaymentConceptAppliesTo::CARRERA, PaymentConceptAppliesTo::CARRERA_SEMESTRE])) {
                $oldCareerIds = $notificationData['old_career_ids'];
                $newCareerIds = $notificationData['new_career_ids'];

                if ($dto->careers && $oldCareerIds != $newCareerIds) {
                    $newCareerUserIds = $this->getUserIdsForNewCareers(
                        $newPaymentConcept,
                        $newAppliesTo
                    );
                    $diff= array_diff($newCareerUserIds, $oldRecipientIds);
                    if (!empty($diff)) {
                        return [
                            'should' => true,
                            'newUserIds' => $diff,
                            'reason' => 'careers_updated',
                            'applies_to' => $newAppliesTo->value
                        ];
                    }
                }
            }

            if (in_array($oldAppliesTo, [PaymentConceptAppliesTo::SEMESTRE, PaymentConceptAppliesTo::CARRERA_SEMESTRE])) {
                $oldSemesters = $notificationData['old_semesters'];
                $newSemesters = $notificationData['new_semesters'];

                if ($dto->semesters && $oldSemesters != $newSemesters) {
                    $newSemesterUserIds = $this->getUserIdsForNewSemesters(
                        $newPaymentConcept,
                        $newAppliesTo
                    );

                    $diff= array_diff($newSemesterUserIds, $oldRecipientIds);

                    if (!empty($diff)) {
                        return [
                            'should' => true,
                            'newUserIds' => $diff,
                            'reason' => 'semesters_updated',
                            'applies_to' => $newAppliesTo->value
                        ];
                    }
                }
            }

            if ($oldAppliesTo === PaymentConceptAppliesTo::ESTUDIANTES && $dto->students) {
                $oldUserIds = $notificationData['old_user_ids'];
                $newUserIds = $notificationData['new_user_ids'];

                $newStudentIds = array_diff($newUserIds, $oldUserIds);

                if (!empty($newStudentIds)) {
                    return [
                        'should' => true,
                        'newUserIds' => $newStudentIds,
                        'reason' => 'students_updated',
                        'applies_to' => $newAppliesTo->value
                    ];
                }
            }

            if ($oldAppliesTo === PaymentConceptAppliesTo::TAG && $dto->applicantTags) {
                $oldTags = $notificationData['old_applicant_tags'];
                $newTags = $notificationData['new_applicant_tags'];

                $newTagUserIds = $this->getUserIdsForNewTags(
                    $newPaymentConcept,
                    $newAppliesTo
                );

                $diff= array_diff($newTagUserIds, $oldRecipientIds);

                if (!empty($diff)) {
                    return [
                        'should' => true,
                        'newUserIds' => $diff,
                        'reason' => 'tags_updated',
                        'applies_to' => $newAppliesTo->value
                    ];
                }
            }
        }

        return [
            'should' => false,
            'newUserIds' => [],
            'reason' => '',
            'applies_to' => $newAppliesTo->value
        ];
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

    private function getUserIdsForNewCareers(PaymentConcept $newPaymentConcept, string $appliesTo): array
    {
        return $this->uqRepo->getRecipientsIds($newPaymentConcept, $appliesTo);
    }

    private function getUserIdsForNewSemesters(PaymentConcept $newPaymentConcept, string $appliesTo): array
    {
        return $this->uqRepo->getRecipientsIds($newPaymentConcept, $appliesTo);
    }

    private function getUserIdsForNewTags(PaymentConcept $newPaymentConcept, string $appliesTo): array
    {
        return $this->uqRepo->getRecipientsIds($newPaymentConcept, $appliesTo);
    }

    private function getReasonMessage(string $reason): string
    {
        return match($reason) {
            'applies_to_changed' => 'El concepto ahora aplica a un grupo diferente de estudiantes',
            'exceptions_removed' => 'Se han eliminado las excepciones del concepto',
            'exceptions_updated' => 'Se han actualizado las excepciones del concepto',
            'careers_updated' => 'Se han agregado nuevas carreras al concepto',
            'semesters_updated' => 'Se han agregado nuevos semestres al concepto',
            'students_updated' => 'Se han agregado nuevos estudiantes al concepto',
            'tags_updated' => 'Se han actualizado los casos especiales del concepto',
            'important_fields_updated' => 'Se han actualizado detalles importantes del concepto',
            default => 'Se ha actualizado el concepto de pago'
        };
    }

}
