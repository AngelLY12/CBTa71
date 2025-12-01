<?php

namespace App\Swagger\controllers;

class Careers
{
/**
 * @OA\Get(
 *     path="/api/v1/careers",
 *     tags={"Careers"},
 *     summary="Obtener todas las carreras",
 *      @OA\Parameter(
 *         name="forceRefresh",
 *         in="query",
 *         description="Forzar actualización del caché (true o false).",
 *         required=false,
 *         @OA\Schema(type="boolean", example=false)
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Lista de carreras",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="data", type="object",
 *                 @OA\Property(property="careers", type="array", @OA\Items(ref="#/components/schemas/DomainCareer"))
 *             ),
 *             @OA\Property(property="message", type="string", example="Carreras encontradas.")
 *         )
 *     )
 * )
 */
public function getCareers(){}


/**
 * @OA\Get(
 *     path="/api/v1/careers/{id}",
 *     tags={"Careers"},
 *     summary="Obtener una carrera por ID",
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="ID de la carrera",
 *         @OA\Schema(type="integer", example=1)
 *     ),
 *      @OA\Parameter(
 *         name="forceRefresh",
 *         in="query",
 *         description="Forzar actualización del caché (true o false).",
 *         required=false,
 *         @OA\Schema(type="boolean", example=false)
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Carrera encontrada",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="data", type="object",
 *                 @OA\Property(property="career", ref="#/components/schemas/DomainCareer")
 *             ),
 *             @OA\Property(property="message", type="string", example="Carrera encontrada.")
 *         )
 *     )
 * )
 */
public function getCareer(){}


/**
 * @OA\Post(
 *     path="/api/v1/careers",
 *     tags={"Careers"},
 *     summary="Crear una nueva carrera",
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(ref="#/components/schemas/CreateCareerRequest")
 *     ),
 *     @OA\Response(
 *         response=201,
 *         description="Carrera creada exitosamente",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="data", type="object",
 *                 @OA\Property(property="career", ref="#/components/schemas/DomainCareer")
 *             ),
 *             @OA\Property(property="message", type="string", example="Carrera creada.")
 *         )
 *     )
 * )
 */
public function createCareer(){}


/**
 * @OA\Patch(
 *     path="/api/v1/careers/{id}",
 *     tags={"Careers"},
 *     summary="Actualizar una carrera existente",
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="ID de la carrera",
 *         @OA\Schema(type="integer", example=1)
 *     ),
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(ref="#/components/schemas/UpdateCareerRequest")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Carrera actualizada exitosamente",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="data", type="object",
 *                 @OA\Property(property="updated", ref="#/components/schemas/DomainCareer")
 *             ),
 *             @OA\Property(property="message", type="string", example="Carrera actualizada.")
 *         )
 *     )
 * )
 */
public function updateCareer(){}


/**
 * @OA\Delete(
 *     path="/api/v1//careers/{id}",
 *     tags={"Careers"},
 *     summary="Eliminar una carrera",
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="ID de la carrera a eliminar",
 *         @OA\Schema(type="integer", example=1)
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Carrera eliminada exitosamente",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="message", type="string", example="Carrera eliminada con éxito.")
 *         )
 *     )
 * )
 */
public function deleteCareer(){}

}

