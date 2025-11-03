<?php

namespace App\Core\Application\Mappers;

use App\Core\Application\DTO\Request\PaymentConcept\CreatePaymentConceptDTO;
use App\Core\Application\DTO\Request\PaymentConcept\UpdatePaymentConceptDTO;
use App\Core\Application\DTO\Response\PaymentConcept\ConceptNameAndAmountResponse;
use App\Core\Application\DTO\Response\PaymentConcept\ConceptsToDashboardResponse;
use App\Core\Application\DTO\Response\PaymentConcept\PendingPaymentConceptsResponse;
use App\Core\Application\DTO\Response\PaymentConcept\PendingSummaryResponse;
use App\Models\PaymentConcept;
use Carbon\Carbon;

class PaymentConceptMapper{
   public static function toCreateConceptDTO(array $data): CreatePaymentConceptDTO
    {
        return new CreatePaymentConceptDTO(
            concept_name: $data['concept_name'],
            description: $data['description'] ?? null,
            amount: $data['amount'],
            status: strtolower($data['status']),
            start_date: isset($data['start_date']) ? new Carbon($data['start_date']) : null,
            end_date: isset($data['end_date']) ? new Carbon($data['end_date']) : null,
            is_global: (bool) $data['is_global'],
            appliesTo: strtolower($data['applies_to'] ?? 'todos'),
            semesters: $data['semestres'] ?? [],
            careers: $data['careers'] ?? [],
            students: $data['students'] ?? []
        );
    }

    public static function toUpdateConceptDTO(array $data): UpdatePaymentConceptDTO
    {
        $fieldsToUpdate = [];
        $allowedFields = ['concept_name', 'description', 'status', 'start_date', 'end_date', 'amount', 'is_global'];
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
            fieldsToUpdate: $fieldsToUpdate,
            semesters: $data['semestres'] ?? [],
            careers: $data['careers'] ?? [],
            students: $data['students'] ?? [],
            appliesTo: $data['applies_to'] ?? null,
            replaceRelations: $data['replaceRelations']
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
            status: $pc->status ?? null,
            amount: $pc->amount ?? null,
            applies_to:$pc->applies_to ?? null,
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
}
