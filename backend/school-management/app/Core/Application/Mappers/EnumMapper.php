<?php

namespace App\Core\Application\Mappers;

use App\Core\Domain\Enum\Payment\PaymentStatus;
use App\Core\Domain\Enum\PaymentConcept\PaymentConceptAppliesTo;
use App\Core\Domain\Enum\PaymentConcept\PaymentConceptStatus;
use App\Core\Domain\Enum\User\UserBloodType;
use App\Core\Domain\Enum\User\UserGender;
use App\Core\Domain\Enum\User\UserStatus;

class EnumMapper
{
    public static function fromStripe(string $stripeStatus): PaymentStatus
    {
            return match ($stripeStatus) {
            'paid' => PaymentStatus::PAID,
            'unpaid' =>PaymentStatus::UNPAID,
            'succeeded' => PaymentStatus::SUCCEEDED,
            'requires_action' => PaymentStatus::REQUIRES_ACTION,

            default => PaymentStatus::DEFAULT,
        };
    }
    public static function toPaymentConceptAppliesTo(string $appliesTo): PaymentConceptAppliesTo
    {
        return PaymentConceptAppliesTo::from($appliesTo);
    }

    public static function toPaymentConceptStatus(string $status): PaymentConceptStatus
    {
        return PaymentConceptStatus::from($status);
    }

    public static function toUserGender(string $gender): UserGender
    {
        return UserGender::from($gender);
    }

    public static function toUserBloodType(string $bloodType): UserBloodType
    {
        return UserBloodType::from($bloodType);
    }

    public static function toUserStatus(string $status): UserStatus
    {
        return UserStatus::from($status);
    }

}
