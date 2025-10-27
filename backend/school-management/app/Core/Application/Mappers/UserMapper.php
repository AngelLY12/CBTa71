<?php

namespace App\Core\Application\Mappers;

use App\Core\Application\DTO\Response\User\UserDataResponse;
use App\Core\Application\DTO\Response\User\UserIdListDTO;
use App\Core\Application\DTO\Response\User\UserRecipientDTO;
use App\Core\Application\DTO\Response\User\UserWithPaymentResponse;
use App\Core\Application\DTO\Response\User\UserWithPendingSumamaryResponse;
use App\Core\Application\DTO\Response\User\UserWithStudentDetailResponse;
use App\Core\Domain\Entities\PaymentConcept;
use App\Models\User as EloquentUser;
use App\Core\Domain\Entities\User as DomainUser;

class UserMapper{

    public static function toDataResponse(DomainUser $user): UserDataResponse{
        return new UserDataResponse(
            id: $user->id ?? null,
            fullName: $user->fullName() ?? null,
            email: $user->email ?? null,
            n_control: $user->studentDetail->n_control ?? null,
            curp: $user->curp ?? null,
        );
    }

    public static function toUserWithPaymentResponse(DomainUser $student,$concept): UserWithPaymentResponse
    {
        return new UserWithPaymentResponse(
            id: $student->id ?? null,
            fullName: $student->fullName() ?? null,
            concept: $concept->concept_name ?? null,
            amount: $concept->amount ?? null
        );
    }

    public static function toRecipientDTO(array $user): UserRecipientDTO
    {
        return new UserRecipientDTO(
            id: $user['id'] ?? null,
            fullName: $user['name'] . ' ' . $user['last_name'] ?? null,
            email: $user['email'] ?? null
        );
    }

    public static function toUserWithStudentDetailResponse(EloquentUser $user): UserWithStudentDetailResponse
    {
        return new UserWithStudentDetailResponse(
            id: $user->id ?? null,
            name: $user->name ?? null,
            last_name: $user->last_name ?? null,
            email: $user->email ?? null,
            phone_number: $user->phone_number ?? null,
            birthdate: $user->birthdate ? $user->birthdate->format('Y-m-d H:i:s'): null,
            gender: $user->gender ?? null,
            curp: $user->curp ?? null,
            address: $user->address ?? null,
            stripe_customer_id: $user->stripe_customer_id ?? null,
            blood_type: $user->blood_type ?? null,
            registration_date: $user->registration_date ? $user->registration_date->format('Y-m-d H:i:s'): null,
            status: $user->status ?? null,
            career_id: $user->studentDetail?->career_id ?? null,
            semestre: $user->studentDetail?->semestre ?? null,
            group: $user->studentDetail?->group ?? null,
            workshop: $user->studentDetail?->workshop ?? null,
            n_control: $user->studentDetail?->n_control ?? null,
        );
    }

    public static function toUserIdListDTO(array $ids): UserIdListDTO
    {
        return new UserIdListDTO(
            userIds:$ids ?? null
        );
    }

    public static function toUserWithPendingSummaryResponse(array $studentSummary): UserWithPendingSumamaryResponse
    {
        return new UserWithPendingSumamaryResponse(
            userId: $studentSummary['user_id'] ?? null,
            fullName: $studentSummary['name'] ?? null,
            semestre: $studentSummary['semestre'] ?? null,
            career_name: $studentSummary['career'] ?? null,
            num_pending: $studentSummary['total_count'] ?? null,
            total_amount_pending: $studentSummary['total_amount'] ?? null
        );
    }
}
