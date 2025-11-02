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













