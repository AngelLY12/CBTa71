<?php

namespace App\Core\Domain\Utils\Validators;

use App\Core\Application\DTO\Request\User\CreateUserDTO;
use App\Exceptions\ValidationException;

class UserValidator
{
    public static function ensureUserDataIsValid(CreateUserDTO $user)
    {
        $bloodTypes = ['O+','O-','A+','A-','B+','B-','AB+','AB-'];
        $status = ['activo','baja','eliminado'];
        $genders =['Hombre','Mujer'];

        if(!in_array($user->status,$status,true))
        {
            throw new ValidationException("El estatus del usuario no es valido");
        }
        if(!in_array($user->gender,$genders,true))
        {
            throw new ValidationException("El genero del usuario no es valido, debe ser Hombre o Mujer");
        }
        if(!in_array($user->blood_type,$bloodTypes,true))
        {
            throw new ValidationException("El tipo de sangre del usuario no es valido");

        }
    }
}
