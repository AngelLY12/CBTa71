<?php

namespace App\Core\Application\Mappers;

use App\Core\Application\DTO\Request\PaymentConcept\CreatePaymentConceptDTO;
use App\Core\Application\DTO\Request\PaymentConcept\UpdatePaymentConceptDTO;
use App\Core\Application\DTO\Request\PaymentConcept\UpdatePaymentConceptRelationsDTO;
use App\Core\Application\DTO\Response\PaymentConcept\ConceptChangeStatusResponse;
use App\Core\Application\DTO\Response\PaymentConcept\ConceptNameAndAmountResponse;
use App\Core\Application\DTO\Response\PaymentConcept\ConceptsToDashboardResponse;
use App\Core\Application\DTO\Response\PaymentConcept\CreatePaymentConceptResponse;
use App\Core\Application\DTO\Response\PaymentConcept\PendingPaymentConceptsResponse;
use App\Core\Application\DTO\Response\PaymentConcept\PendingSummaryResponse;
use App\Core\Application\DTO\Response\PaymentConcept\UpdatePaymentConceptRelationsResponse;
use App\Core\Application\DTO\Response\PaymentConcept\UpdatePaymentConceptResponse;
use App\Core\Domain\Entities\PaymentConcept as EntitiesPaymentConcept;
use App\Core\Domain\Enum\PaymentConcept\PaymentConceptApplicantType;
use App\Core\Domain\Enum\PaymentConcept\PaymentConceptAppliesTo;
use App\Core\Domain\Enum\PaymentConcept\PaymentConceptStatus;
use App\Models\PaymentConcept;
use App\Models\PaymentConceptApplicantTag;
use Carbon\Carbon;

class PaymentConceptMapper{


    public static function toDomain(CreatePaymentConceptDTO $dto): EntitiesPaymentConcept
    {
        return new EntitiesPaymentConcept(
            id: null,
            concept_name: $dto->concept_name,
            description: $dto->description,
            status: $dto->status,
            start_date: $dto->start_date,
            end_date: $dto->end_date,
            amount: $dto->amount,
            applies_to: $dto->appliesTo,
            is_global: $dto->is_global
        );
    }

   public static function toCreateConceptDTO(array $data): CreatePaymentConceptDTO
    {
        $statusEnum = isset($data['status'])
            ? PaymentConceptStatus::from(strtolower($data['status']))
            : PaymentConceptStatus::ACTIVO;

        $appliesToEnum = isset($data['applies_to'])
            ? PaymentConceptAppliesTo::from(strtolower($data['applies_to']))
            : PaymentConceptAppliesTo::TODOS;


        return new CreatePaymentConceptDTO(
            concept_name: $data['concept_name'],
            description: $data['description'] ?? null,
            amount: number_format($data['amount'], 2, '.', ''),
            status: $statusEnum,
            start_date: isset($data['start_date']) ? new Carbon($data['start_date']) : null,
            end_date: isset($data['end_date']) ? new Carbon($data['end_date']) : null,
            is_global: (bool) ($data['is_global'] ?? false),
            appliesTo: $appliesToEnum,
            semesters: $data['semestres'] ?? [],
            careers: $data['careers'] ?? [],
            students: $data['students'] ?? [],
            exceptionStudents: $data['exceptionStudents'] ?? [],
            applicantTags: $data['applicantTags'] ?? [],
        );
    }

    public static function toUpdateConceptDTO(array $data): UpdatePaymentConceptDTO
    {
        $fieldsToUpdate = [];
        $allowedFields = ['concept_name', 'description', 'start_date', 'end_date', 'amount'];
        foreach ($allowedFields as $field) {
            if (array_key_exists($field, $data)) {
                if (in_array($field, ['start_date', 'end_date']) && $data[$field] !== null) {
                    $fieldsToUpdate[$field] = new \Carbon\Carbon($data[$field]);
                } else {
                    $fieldsToUpdate[$field] = $data[$field];
                }
            }
        }

        return new UpdatePaymentConceptDTO(
            id: (int) $data['id'],
            concept_name: $fieldsToUpdate['concept_name'] ?? null,
            description: $fieldsToUpdate['description'] ?? null,
            start_date: $fieldsToUpdate['start_date'] ?? null,
            end_date: $fieldsToUpdate['end_date'] ?? null,
            amount: array_key_exists('amount', $fieldsToUpdate) ? number_format($fieldsToUpdate['amount'],2,'.','') : null,
        );
    }

    public static function toUpdateConceptRelationsDTO(array $data): UpdatePaymentConceptRelationsDTO
    {
        $appliesToEnum = isset($data['applies_to'])
            ? PaymentConceptAppliesTo::from(strtolower($data['applies_to']))
            : null;
        $applicantTagToEnum = isset($data['applicantTags'])
            ? PaymentConceptAppliesTo::from(strtolower($data['applicantTags']))
            : [];
        return new UpdatePaymentConceptRelationsDTO(
            id: (int) $data['id'],
            semesters: $data['semesters'] ?? [],
            careers: $data['careers'] ?? [],
            students: $data['students'] ?? [],
            appliesTo: $appliesToEnum,
            is_global: $data['is_global'] ?? null,
            replaceRelations: $data['replaceRelations'] ?? false,
            exceptionStudents: $data['exceptionStudents'] ?? [],
            replaceExceptions: $data['replaceExceptions'] ?? false,
            removeAllExceptions: $data['removeAllExceptions'] ?? false,
            applicantTags:$applicantTagToEnum,
        );
    }


    public static function toPendingPaymentConceptResponse(array $pc): PendingPaymentConceptsResponse {
        return new PendingPaymentConceptsResponse(
            id: $pc['id'] ?? null,
            concept_name: $pc['concept_name'] ?? null,
            description: $pc['description'] ?? null,
            amount: $pc['amount'] ?? null,
            start_date: date('Y-m-d H:i:s', strtotime($pc['start_date'])) ?? null,
            end_date: $pc['end_date'] ? date('Y-m-d H:i:s', strtotime($pc['end_date'])) : null
        );
    }

    public static function toConceptsToDashboardResponse(PaymentConcept $pc): ConceptsToDashboardResponse {
        return new ConceptsToDashboardResponse(
            id: $pc->id ?? null,
            concept_name: $pc->concept_name ?? null,
            status: $pc->status->value ?? null,
            amount: $pc->amount ?? null,
            applies_to:$pc->applies_to->value ?? null,
            start_date: $pc->start_date ? $pc->start_date->format('Y-m-d H:i:s') : null,
            end_date: $pc->end_date ? $pc->end_date->format('Y-m-d H:i:s') : null
        );

    }
    public static function toPendingPaymentSummary(array $data):PendingSummaryResponse
    {
        return new PendingSummaryResponse(
            totalAmount:$data['total_amount'] ?? null,
            totalCount:$data['total_count'] ?? null
        );

    }
    public static function toConceptNameAndAmoutResonse(array $data): ConceptNameAndAmountResponse
    {
        return new ConceptNameAndAmountResponse(
            user_name: $data['user_name'] ?? null,
            concept_name: $data['concept_name'] ?? null,
            amount:$data['amount'] ?? null
        );
    }

    public static function toCreatePaymentConceptResponse(\App\Core\Domain\Entities\PaymentConcept $paymentConcept, int $affectedCount): CreatePaymentConceptResponse
    {
        return new CreatePaymentConceptResponse(
            id: $paymentConcept->id,
            conceptName: $paymentConcept->concept_name,
            status: $paymentConcept->status->value,
            appliesTo: $paymentConcept->applies_to->value,
            description: $paymentConcept->description,
            amount: $paymentConcept->amount,
            startDate: $paymentConcept->start_date->format('Y-m-d'),
            endDate: $paymentConcept->end_date->format('Y-m-d'),
            affectedStudentsCount: $affectedCount,
            metadata: [
                'is_global' => $paymentConcept->is_global,
                'exception_count' => count($paymentConcept->getExceptionUsersIds()),
                'career_count' => count($paymentConcept->getCareerIds()),
                'semester_count' => count($paymentConcept->getSemesters()),
            ],
            message: sprintf(
                'Concepto creado exitosamente. Afecta a %d estudiante(s)',
                $affectedCount
            ),
            createdAt: now()->format('Y-m-d H:i:s'),
        );
    }

    public static function toUpdatePaymentConceptResponse(EntitiesPaymentConcept $newPaymentConcept, array $data): UpdatePaymentConceptResponse
    {
        return new UpdatePaymentConceptResponse(
            id: $newPaymentConcept->id,
            conceptName: $newPaymentConcept->concept_name,
            status: $newPaymentConcept->status->value,
            appliesTo: $newPaymentConcept->applies_to->value,
            description: $newPaymentConcept->description,
            amount: $newPaymentConcept->amount,
            startDate: $newPaymentConcept->start_date->format('Y-m-d'),
            endDate: $newPaymentConcept->end_date->format('Y-m-d'),
            message: $data['message'] ?? null,
            updatedAt: now()->format('Y-m-d H:i:s'),
            changes: $data['changes'] ?? [],
        );
    }
    public static function toUpdatePaymentConceptRelationsResponse(EntitiesPaymentConcept $newPaymentConcept, array $data): UpdatePaymentConceptRelationsResponse
    {
        return new UpdatePaymentConceptRelationsResponse(
            status: $newPaymentConcept->status->value,
            metadata: [
                'concept_name' => $newPaymentConcept->concept_name,
                'global_status' => $newPaymentConcept->is_global,
                'students_count' => count($newPaymentConcept->getUserIds()),
                'exception_count' => count($newPaymentConcept->getExceptionUsersIds()),
                'career_count' => count($newPaymentConcept->getCareerIds()),
                'semester_count' => count($newPaymentConcept->getSemesters()),
                'tags' => [$newPaymentConcept->getApplicantTag()]
            ],
            message: $data['message'] ?? null,
            updatedAt: now()->format('Y-m-d H:i:s'),
            changes: $data['changes'] ?? [],
            affectedSummary: $data['affectedSummary'] ?? []
        );
    }

    public static function toConceptChangeStatusResponse(EntitiesPaymentConcept $paymentConcept, array $data): ConceptChangeStatusResponse
    {
        return new ConceptChangeStatusResponse(
            conceptData: [
                'id' => $paymentConcept->id,
                'concept_name' => $paymentConcept->concept_name,
                'status' => $paymentConcept->status->value,
                'amount' => $paymentConcept->amount,
                'start_date' => $paymentConcept->start_date->format('Y-m-d'),
                'end_date' => $paymentConcept->end_date->format('Y-m-d'),
                'applies_to' => $paymentConcept->applies_to->value,
            ],
            message: $data['message'] ?? null,
            changes: $data['changes'] ?? [],
            updatedAt: now()->format('Y-m-d H:i:s'),
        );
    }
}
