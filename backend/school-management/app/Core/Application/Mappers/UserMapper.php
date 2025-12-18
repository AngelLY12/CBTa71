<?php

namespace App\Core\Application\Mappers;

use App\Core\Application\DTO\Request\User\CreateUserDTO;
use App\Core\Application\DTO\Request\User\UpdateUserPermissionsDTO;
use App\Core\Application\DTO\Request\User\UpdateUserRoleDTO;
use App\Core\Application\DTO\Response\User\PromotedStudentsResponse;
use App\Core\Application\DTO\Response\User\UserChangedStatusResponse;
use App\Core\Application\DTO\Response\User\UserDataResponse;
use App\Core\Application\DTO\Response\User\UserIdListDTO;
use App\Core\Application\DTO\Response\User\UserRecipientDTO;
use App\Core\Application\DTO\Response\User\UserWithPaymentResponse;
use App\Core\Application\DTO\Response\User\UserWithPendingSumamaryResponse;
use App\Core\Application\DTO\Response\User\UserWithStudentDetailResponse;
use App\Core\Application\DTO\Response\User\UserWithUpdatedPermissionsResponse;
use App\Core\Application\DTO\Response\User\UserWithUpdatedRoleResponse;
use App\Models\User as EloquentUser;
use App\Core\Domain\Entities\User as DomainUser;
use App\Core\Domain\Enum\User\UserBloodType;
use App\Core\Domain\Enum\User\UserGender;
use App\Core\Domain\Enum\User\UserStatus;
use Carbon\Carbon;

class UserMapper{

    public static function toDomain(CreateUserDTO $user): DomainUser
    {
        return new DomainUser(
            name: $user->name,
            last_name: $user->last_name,
            email: $user->email,
            password: $user->password,
            phone_number: $user->phone_number,
            birthdate: $user->birthdate ?? null,
            gender: $user->gender ?? null,
            curp: $user->curp ?? null,
            address: $user->address ?? [],
            stripe_customer_id: null,
            blood_type: $user->blood_type ?? null,
            registration_date: $user->registration_date ?? null,
            status: $user->status
        );

    }

    public static function toCreateUserDTO(array $data): CreateUserDTO
    {
        return new CreateUserDTO(
            name: $data['name'],
            last_name: $data['last_name'],
            email: $data['email'],
            password: $data['password'],
            phone_number: $data['phone_number'],
            birthdate: new Carbon($data['birthdate']) ?? null,
            gender: isset($data['gender'])
            ? UserGender::from(strtolower($data['gender']))
            : null,
            curp:$data['curp'],
            address: $data['address'] ?? [],
             blood_type: isset($data['blood_type'])
            ? UserBloodType::from($data['blood_type'])
            : null,
            registration_date: new Carbon($data['registration_date'] ?? Carbon::now()),
            status: UserStatus::from($data['status'])
        );

    }

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
            gender: $user->gender->value ?? null,
            curp: $user->curp ?? null,
            address: $user->address ?? null,
            stripe_customer_id: $user->stripe_customer_id ?? null,
            blood_type: $user->blood_type->value ?? null,
            registration_date: $user->registration_date ? $user->registration_date->format('Y-m-d H:i:s'): null,
            status: $user->status->value ?? null,
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
            roles: $studentSummary['roles'] ?? null,
            semestre: $studentSummary['semestre'] ?? null,
            career_name: $studentSummary['career'] ?? null,
            num_pending: $studentSummary['total_count'] ?? null,
            num_expired: $studentSummary['expired_count'] ?? null,
            total_amount_pending: $studentSummary['total_amount'] ?? null,
            total_paid: $studentSummary['total_paid'] ?? null,
            expired_amount: $studentSummary['expired_amount'] ?? null,
            num_paid: $studentSummary['total_paid_concepts'] ?? null,
        );
    }

    public static function toUpdateUserPermissionsDTO(array $data): UpdateUserPermissionsDTO
    {
        return new UpdateUserPermissionsDTO(
            curps: $data['curps'] ?? [],
            role: $data['role'] ?? null,
            permissionsToAdd: $data['permissionsToAdd'] ?? [],
            permissionsToRemove: $data['permissionsToRemove'] ?? []

        );
    }

    public static function toUserUpdatedPermissionsResponse(?EloquentUser $user=null, array $permissions, ?string $role=null, int $totalUpdated): UserWithUpdatedPermissionsResponse
    {
        return new UserWithUpdatedPermissionsResponse(
            fullName: $user?->name && $user?->last_name ? "{$user->name} {$user->last_name}" : null,
            curp: $user?->curp ?? null,
            role: $role,
            updatedPermissions: $permissions,
            totalUpdated: $totalUpdated ?? 0
        );
    }
    public static function toUpdateUserRoleDTO(array $data): UpdateUserRoleDTO
    {
        return new UpdateUserRoleDTO(
            curps:$data['curps'] ?? [],
            rolesToAdd:$data['rolesToAdd'] ?? [],
            rolesToRemove:$data['rolesToRemove'] ?? []
        );
    }

    public static function toUserWithUptadedRoleResponse(array $data): UserWithUpdatedRoleResponse
    {
        return new UserWithUpdatedRoleResponse(
            fullNames:$data['names'] ?? [],
            curps: $data['curps'] ?? [],
            updatedRoles:$data['roles'] ?? [],
            totalUpdated: $data['totalUpdated'] ?? 0
        );
    }

    public static function toUserChangedStatusResponse(array $data): UserChangedStatusResponse
    {
        return new UserChangedStatusResponse(
            updatedUsers: $data['users'],
            newStatus: $data['status'],
            totalUpdated: $data['total'] ?? 0
        );
    }

    public static function toPromotedStudentsResponse(array $data): PromotedStudentsResponse
    {
        return new PromotedStudentsResponse(
            promotedStudents: $data['promotedStudents'] ?? 0,
            desactivatedStudents: $data['desactivatedStudents'] ?? 0,
        );
    }

}
