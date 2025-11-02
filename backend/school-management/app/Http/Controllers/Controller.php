<?php

namespace App\Http\Controllers;
/**
 * @OA\Info(
 *     version="1.0.0",
 *     title="School Management API",
 *     description="Documentación de la API para el sistema de gestión escolar",
 *     @OA\Contact(
 *         email="soporte@cbta71.edu.mx",
 *         name="Equipo de desarrollo CBTa71"
 *     ),
 *     @OA\License(
 *         name="MIT",
 *         url="https://opensource.org/licenses/MIT"
 *     )
 * )
 *
 * @OA\Server(
 *     url="http://localhost:9000/api",
 *     description="Servidor local"
 * )
 *
 * @OA\Server(
 *     url="https://cbta71-production.up.railway.app/api",
 *     description="Servidor de producción"
 * )
 */
abstract class Controller
{
    //
}
