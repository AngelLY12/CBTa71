<?php

namespace App\Core\Domain\Utils\Validators;

use App\Core\Application\DTO\Request\User\CreateUserDTO;
use App\Core\Domain\Entities\User;
use App\Core\Domain\Enum\User\UserBloodType;
use App\Core\Domain\Enum\User\UserGender;
use App\Core\Domain\Enum\User\UserStatus;
use App\Exceptions\Conflict\UserAlreadyActiveException;
use App\Exceptions\Conflict\UserAlreadyDeletedException;
use App\Exceptions\Conflict\UserAlreadyDisabledException;
use App\Exceptions\Conflict\UserCannotBeDisabledException;
use App\Exceptions\ValidationException;

class UserValidator
{
    public static function ensureUserDataIsValid(CreateUserDTO $user)
    {
        if (UserStatus::tryFrom($user->status->value) === null) {
            throw new ValidationException("El estatus del usuario no es valido");
        }

        if (UserGender::tryFrom(strtolower($user->gender->value)) === null) {
            throw new ValidationException("El genero del usuario no es valido, debe ser hombre o mujer");
        }

        if (UserBloodType::tryFrom($user->blood_type->value) === null) {
            throw new ValidationException("El tipo de sangre del usuario no es valido");
        }
    }

    public static function ensureValidStatusTransition(User $user, UserStatus $newStatus)
    {
        switch ($newStatus) {

            case UserStatus::ELIMINADO:
                if ($user->isDeleted()) {
                    throw new UserAlreadyDeletedException();
                }
                break;

            case UserStatus::BAJA:
                if ($user->isDeleted()) {
                    throw new UserCannotBeDisabledException();
                }
                if ($user->isDisable()) {
                    throw new UserAlreadyDisabledException();
                }
                break;

            case UserStatus::ACTIVO:
                if ($user->isActive()) {
                    throw new UserAlreadyActiveException();
                }
                break;
        }
    }

}
