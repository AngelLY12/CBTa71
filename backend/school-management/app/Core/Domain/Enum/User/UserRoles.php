<?php

namespace App\Core\Domain\Enum\User;

enum UserRoles: string
{
    case STUDENT = 'student';
    case FINANCIAL_STAFF = 'financial-staff';
    case PARENT = 'parent';
    case UNVERIFIED = 'unverified';
    case ADMIN = 'admin';
    case SUPERVISOR = 'supervisor';
    case APPLICANT = 'applicant';

    public static function values(): array
    {
        return array_map(fn($role) => $role->value, self::cases());
    }

    public static function students(): array
    {
        return [
            self::STUDENT,
            self::APPLICANT,
        ];
    }

    public static function administrationRoles(): array
    {
        return [self::ADMIN->value, self::SUPERVISOR->value];
    }

}
