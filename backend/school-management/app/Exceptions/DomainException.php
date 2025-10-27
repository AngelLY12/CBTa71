<?php

namespace App\Exceptions;

use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Excepción base para errores de dominio.
 */
abstract class DomainException extends HttpException
{
    public function __construct(int $statusCode, string $message)
    {
        parent::__construct($statusCode, $message);
    }
}

/**
 * Excepción cuando un concepto no se encuentra.
 */
class ConceptNotFoundException extends DomainException
{
    public function __construct()
    {
        parent::__construct(404, 'El concepto solicitado no fue encontrado.');
    }
}

/**
 * Excepción cuando se produce un conflicto entre el ámbito global y destinatarios específicos.
 */
class ConceptAppliesToConflictException extends DomainException
{
    public function __construct()
    {
        parent::__construct(422, 'Si el concepto aplica a todos, no se deben enviar carreras, semestres o estudiantes específicos.');
    }
}

/**
 * Excepción cuando las carreras no existen.
 */
class CareersNotFoundException extends DomainException
{
    public function __construct()
    {
        parent::__construct(404, 'La o las carreras no existen.');
    }
}

/**
 * Excepción cuando no se especifican semestres válidos.
 */
class SemestersNotFoundException extends DomainException
{
    public function __construct()
    {
        parent::__construct(422, 'No se especificaron semestres.');
    }
}

/**
 * Excepción cuando los estudiantes no existen o están dados de baja.
 */
class StudentsNotFoundException extends DomainException
{
    public function __construct()
    {
        parent::__construct(404, 'Ninguno de los estudiantes existe o está dado de baja.');
    }
}

/**
 * Excepción cuando no se especifican carreras o semestres válidos.
 */
class CareerSemesterInvalidException extends DomainException
{
    public function __construct()
    {
        parent::__construct(422, 'Debe especificar al menos una carrera y un semestre.');
    }
}

/**
 * Excepción cuando no se encuentran destinatarios válidos para un concepto.
 */
class RecipientsNotFoundException extends DomainException
{
    public function __construct()
    {
        parent::__construct(404, 'No se encontraron destinatarios válidos para el concepto de pago.');
    }
}

/**
 * Excepciones relacionadas con el estado y validación de conceptos.
 */
class ConceptInactiveException extends DomainException
{
    public function __construct()
    {
        parent::__construct(422, 'El concepto no está activo.');
    }
}

class ConceptNotStartedException extends DomainException
{
    public function __construct()
    {
        parent::__construct(422, 'El concepto no ha iniciado, no puede ser finalizado.');
    }
}

class ConceptAlreadyActiveException extends DomainException
{
    public function __construct()
    {
        parent::__construct(422, 'El concepto ya está activo.');
    }
}

class ConceptAlreadyDisabledException extends DomainException
{
    public function __construct()
    {
        parent::__construct(422, 'El concepto ya está desactivado.');
    }
}

class ConceptCannotBeDisabledException extends DomainException
{
    public function __construct()
    {
        parent::__construct(422, 'No se puede desactivar un concepto finalizado.');
    }
}

class ConceptAlreadyFinalizedException extends DomainException
{
    public function __construct()
    {
        parent::__construct(422, 'El concepto ya está finalizado.');
    }
}

class ConceptCannotBeFinalizedException extends DomainException
{
    public function __construct()
    {
        parent::__construct(422, 'No se puede finalizar un concepto eliminado.');
    }
}

class ConceptAlreadyDeletedException extends DomainException
{
    public function __construct()
    {
        parent::__construct(422, 'El concepto ya está eliminado.');
    }
}

class ConceptInvalidStatusException extends DomainException
{
    public function __construct()
    {
        parent::__construct(422, 'Estado no válido.');
    }
}

class ConceptCannotBeUpdatedException extends DomainException
{
    public function __construct()
    {
        parent::__construct(422, 'No se puede actualizar un concepto que no esté activo o desactivado.');
    }
}

class ConceptMissingNameException extends DomainException
{
    public function __construct()
    {
        parent::__construct(422, 'El concepto debe tener un nombre válido.');
    }
}

class ConceptInvalidAmountException extends DomainException
{
    public function __construct()
    {
        parent::__construct(422, 'El monto del concepto debe ser mayor a 10.');
    }
}

class ConceptInvalidStartDateException extends DomainException
{
    public function __construct()
    {
        parent::__construct(422, 'La fecha de inicio del concepto no es válida.');
    }
}

class ConceptStartDateTooFarException extends DomainException
{
    public function __construct()
    {
        parent::__construct(422, 'La fecha de inicio del concepto no puede ser más de 1 mes después de hoy.');
    }
}

class ConceptStartDateTooEarlyException extends DomainException
{
    public function __construct()
    {
        parent::__construct(422, 'La fecha de inicio del concepto no puede ser más de 1 mes antes de hoy.');
    }
}

class ConceptInvalidEndDateException extends DomainException
{
    public function __construct()
    {
        parent::__construct(422, 'La fecha de fin del concepto no es válida.');
    }
}

class ConceptEndDateBeforeStartException extends DomainException
{
    public function __construct()
    {
        parent::__construct(422, 'La fecha de fin no puede ser anterior a la fecha de inicio.');
    }
}

class ConceptEndDateBeforeTodayException extends DomainException
{
    public function __construct()
    {
        parent::__construct(422, 'La fecha de fin no puede ser anterior a hoy.');
    }
}

class ConceptEndDateTooFarException extends DomainException
{
    public function __construct()
    {
        parent::__construct(422, 'La fecha de fin no puede exceder 5 años desde la fecha de inicio.');
    }
}

/**
 * Excepciones relacionada con un pago valido para ser pagado.
 */

class ConceptExpiredException extends DomainException
{
    public function __construct()
    {
        parent::__construct(422,"El concepto no está vigente para pago.");
    }
}

/**
 * Excepciones relacionada cuando haya conflicto por un pago ya pagado.
 */

class PaymentAlreadyExistsException extends DomainException
{
    public function __construct()
    {
        parent::__construct(409,"El concepto ya fue pagado por el usuario.");
    }
}

class PaymentMethodNotSupportedException extends DomainException
{
    public function __construct()
    {
        parent::__construct(422,"El método de pago no es soportado.");
    }
}

class StripeCheckoutSessionException extends DomainException
{
    public function __construct()
    {
        parent::__construct(500, "Ocurrió un error al crear la sesión de pago en Stripe.");
    }
}

class UserNotAllowedException extends DomainException
{
    public function __construct()
    {
        parent::__construct(403,"El usuario no tiene permitido pagar este concepto.");
    }
}

class InvalidCredentialsException extends DomainException
{
    public function __construct()
    {
        parent::__construct(422, 'Las credenciales son incorrectas.');
    }
}

class UserInactiveException extends DomainException
{
    public function __construct()
    {
        parent::__construct(422, 'El usuario está dado de baja.');
    }
}

class PaymentMethodNotFoundException extends DomainException
{
    public function __construct()
    {
        parent::__construct(404, 'Método de pago no encontrado.');
    }
}

class PaymentReconciliationException extends DomainException
{
    public function __construct(string $details)
    {
        parent::__construct(500, "Error al reconciliar el pago: $details");
    }
}

class PaymentNotificationException extends DomainException
{
    public function __construct(string $details)
    {
        parent::__construct(500, "Error al notificar al usuario: $details");
    }
}

class UserNotFoundException extends DomainException
{
    public function __construct()
    {
        parent::__construct(404, "Usuario no encontrado");
    }
}



