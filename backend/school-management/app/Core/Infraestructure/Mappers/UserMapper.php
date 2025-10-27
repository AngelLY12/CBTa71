<?php

namespace App\Core\Infraestructure\Mappers;

use App\Core\Application\DTO\User\UserDataDTO;
use App\Core\Application\DTO\User\UserPaymentDTO;
use App\Core\Application\DTO\User\UserRecipientDTO;
use App\Core\Application\DTO\User\UserWithStudentDetail;
use App\Models\User as EloquentUser;
use App\Core\Domain\Entities\User as DomainUser;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;

class UserMapper{
    public static function toDomain(EloquentUser $user): DomainUser
    {

        $domainUser = new DomainUser(
            id: $user->id,
            name: $user->name,
            last_name: $user->last_name,
            email: $user->email,
            password: $user->password ?? '',
            phone_number: $user->phone_number ?? '',
            birthdate: $user->birthdate ?? null,
            gender: $user->gender ?? '',
            curp: $user->curp ?? '',
            address: $user->address ?? [],
            stripe_customer_id: $user->stripe_customer_id ?? null,
            blood_type: $user->blood_type ?? '',
            registration_date: $user->registration_date ?? null,
            status: $user->status
        );
        if ($user->studentDetail) {
            $domainUser->setStudentDetail(StudentDetailMapper::toDomain($user->studentDetail));
        }
        return $domainUser;

    }
    public static function toPersistence(DomainUser $user): array
    {
        return [
            'name' => $user->name,
            'last_name' => $user->last_name,
            'email' => $user->email,
            'password' => Hash::make($user->password),
            'phone_number' =>$user->phone_number,
            'birthdate' => $user->birthdate,
            'gender' => $user->gender,
            'curp' => $user->curp,
            'address' => $user->address,
            'stripe_customer_id' => $user->stripe_customer_id ?? null,
            'blood_type' => $user->blood_type,
            'registration_date' => $user->registration_date,
            'status' => $user->status,
        ];
    }
}
