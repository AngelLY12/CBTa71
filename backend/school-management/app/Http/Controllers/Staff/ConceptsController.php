<?php

namespace App\Http\Controllers\Staff;

use App\Core\Application\Mappers\PaymentConceptMapper;
use App\Core\Infraestructure\Mappers\PaymentConceptMapper as InfraPaymentConceptMapper;
use App\Core\Application\Services\Payments\Staff\ConceptsServiceFacades;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\PaymentConcept;

/**
 * @OA\Tag(
 *     name="Payment Concepts",
 *     description="Gestión de conceptos de pago en el sistema (creación, actualización, activación, eliminación, etc.)"
 * )
 */
class ConceptsController extends Controller
{
    protected ConceptsServiceFacades $conceptsService;

    public function __construct(ConceptsServiceFacades $conceptsService)
    {
        $this->conceptsService= $conceptsService;


    }

    /**
     * @OA\Get(
     *     path="/api/v1/concepts",
     *     summary="Listar conceptos de pago",
     *     description="Obtiene una lista paginada de conceptos de pago, con filtros opcionales por estado y control de caché.",
     *     tags={"Payment Concepts"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="Filtra por estado (ej. 'activo', 'todos', etc)",
     *         required=false,
     *         @OA\Schema(type="string", example="activo")
     *     ),
     *     @OA\Parameter(
     *         name="perPage",
     *         in="query",
     *         description="Cantidad de registros por página",
     *         required=false,
     *         @OA\Schema(type="integer", example=15)
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Número de página a obtener",
     *         required=false,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="forceRefresh",
     *         in="query",
     *         description="Si es true, fuerza actualización de caché",
     *         required=false,
     *         @OA\Schema(type="boolean", example=false)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Listado de conceptos de pago obtenido exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(
     *                     property="concepts",
     *                     allOf={
     *                         @OA\Schema(ref="#/components/schemas/PaginatedResponse"),
     *                         @OA\Schema(
     *                             @OA\Property(
     *                                 property="items",
     *                                 type="array",
     *                                 @OA\Items(ref="#/components/schemas/DomainPaymentConcept")
     *                             )
     *                         )
     *                     }
     *                 )
     *             ),
     *             @OA\Property(property="message", type="string", nullable=true)
     *         )
     *     )
     * )
     */
    public function index(Request $request)
    {
        $status = strtolower($request->query('status','todos'));
        $forceRefresh = filter_var($request->query('forceRefresh', false), FILTER_VALIDATE_BOOLEAN);
        $perPage = $request->query('perPage', 15);
        $page    = $request->query('page', 1);

        $paginatedData = $this->conceptsService->showConcepts($status, $perPage, $page, $forceRefresh);
        return response()->json([
                'success' => true,
                'data' => ['concepts'=>$paginatedData],
                'message'=>empty($paginatedData) ? 'No hay conceptos de pago creados' : null
            ]);

    }

    /**
     * @OA\Post(
     *     path="/api/v1/concepts",
     *     summary="Crear un nuevo concepto de pago",
     *     description="Crea un nuevo concepto de pago y lo asocia con las entidades correspondientes (carreras, semestres, estudiantes).",
     *     tags={"Payment Concepts"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/CreatePaymentConceptDTO")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Concepto de pago creado exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="concept",
     *                     ref="#/components/schemas/DomainPaymentConcept"
     *                 )
     *             ),
     *             @OA\Property(property="message", type="string", example="Concepto de pago creado con éxito.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Error en la validación de datos",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Error en la validación de datos."),
     *             @OA\Property(property="errors", type="object", description="Errores de validación por campo")
     *         )
     *     ),
     *     @OA\Response(
     *         response=409,
     *         description="Conflicto en los datos",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Conflicto en los datos.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Recurso no encontrado",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Recurso no encontrado.")
     *         )
     *     )
     * )
     */
    public function store(Request $request)
    {
        $data = $request->only([
            'concept_name',
            'description',
            'status',
            'start_date',
            'end_date',
            'amount',
            'is_global',
            'applies_to',
            'semestres',
            'careers',
            'students'
        ]);
        $rules = [
            'concept_name'  =>'required|string|max:50',
            'description'   =>'nullable|string|max:100',
            'status'        =>'required|string',
            'start_date'    =>'required|date',
            'end_date'      =>'nullable|date',
            'amount'        =>'required|numeric',
            'is_global'     =>'required|boolean',
            'applies_to'            =>'required|string',
            'semestres'              =>'nullable|array',
            'careers'                =>'nullable|array',
            'students'              =>'nullable|array'

        ];

        $validator = Validator::make($data,$rules);
        if($validator->fails()){
            return response()->json([
            'success' => false,
            'errors'  => $validator->errors(),
            'message' => 'Error en la validación de datos.'
        ], 422);

        }
        $dto = PaymentConceptMapper::toCreateConceptDTO($data);

        $createdConcept=$this->conceptsService->createPaymentConcept($dto);

        return response()->json([
            'success' => true,
            'data' => ['concept' => $createdConcept],
            'message' => 'Concepto de pago creado con éxito.',
        ], 201);

    }

   /**
     * @OA\Put(
     *     path="/api/v1/concepts/{id}",
     *     summary="Actualizar un concepto de pago",
     *     description="Actualiza los datos de un concepto de pago existente. Todos los campos son opcionales (usar 'sometimes'), excepto el id en la ruta.",
     *     tags={"Payment Concepts"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID del concepto de pago",
     *         @OA\Schema(type="integer", example=5)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/UpdatePaymentConceptDTO")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Concepto actualizado correctamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(
     *                     property="concept",
     *                     ref="#/components/schemas/DomainPaymentConcept"
     *                 )
     *
     *             ),
     *             @OA\Property(property="message", type="string", example="Concepto de pago actualizado correctamente.")
     *         )
     *     ),
     *     @OA\Response(response=422, description="Error en la validación de datos"),
     *     @OA\Response(response=409, description="Conflicto en los datos"),
     *     @OA\Response(response=404, description="Recurso no encontrado")
     * )
     */
      public function update(Request $request, PaymentConcept $concept)
    {
        $data = $request->only([
            'concept_name',
            'description',
            'status',
            'start_date',
            'end_date',
            'amount',
            'is_global',
            'applies_to',
            'semestres',
            'careers',
            'students',
            'replaceRelations'
        ]);

        $rules = [
            'concept_name'  => 'sometimes|required|string|max:50',
            'description'   => 'nullable|string|max:100',
            'status'        => 'sometimes|required|string',
            'start_date'    => 'sometimes|required|date',
            'end_date'      => 'nullable|date',
            'amount'        => 'sometimes|required|numeric',
            'is_global'     => 'sometimes|required|boolean',
            'applies_to'    => 'nullable|string|in:carrera,semestre,estudiantes,todos',
            'semestres'      => 'nullable|array',
            'careers'        => 'nullable|array',
            'students'      => 'nullable|array',
            'replaceRelations' => 'sometimes|required|boolean'
        ];

        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors(),
                'message' => 'Error en la validación de datos.'
            ], 422);
        }
        $data['id'] = $concept->id;
        $dto = PaymentConceptMapper::toUpdateConceptDTO($data);

        $updatedConcept = $this->conceptsService->updatePaymentConcept($dto);

        return response()->json([
            'success' => true,
            'data' => ['concept'=>$updatedConcept],
            'message' => 'Concepto de pago actualizado correctamente.'
        ]);
    }


    /**
     * @OA\Post(
     *     path="/api/v1/concepts/{id}/finalize",
     *     summary="Finalizar concepto de pago",
     *     tags={"Payment Concepts"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(
     *         response=200,
     *         description="Concepto finalizado correctamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(
     *                     property="concept",
     *                     ref="#/components/schemas/DomainPaymentConcept"
     *                 )
     *
     *             ),
     *             @OA\Property(property="message", type="string", example="Concepto de pago finalizado correctamente.")
     *         )
     *     ),
     *     @OA\Response(response=409, description="Conflicto en los datos")
     * )
     */
    public function finalize(PaymentConcept $concept)
    {
        $domainConcept = InfraPaymentConceptMapper::toDomain($concept);
        $finalized = $this->conceptsService->finalizePaymentConcept($domainConcept);

        return response()->json([
            'success' => true,
            'data' => ['concept'=>$finalized],
            'message' => 'Concepto de pago finalizado correctamente.'
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/concepts/{id}/disable",
     *     summary="Deshabilitar un concepto de pago",
     *     tags={"Payment Concepts"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(
     *         response=200,
     *         description="Concepto deshabilitado correctamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(
     *                     property="concept",
     *                     ref="#/components/schemas/DomainPaymentConcept"
     *                 )
     *
     *             ),
     *             @OA\Property(property="message", type="string", example="Concepto de pago deshabilitado correctamente.")
     *         )
     *     ),
     *     @OA\Response(response=409, description="Conflicto en los datos")
     * )
     */
    public function disable(PaymentConcept $concept)
    {
        $domainConcept = InfraPaymentConceptMapper::toDomain($concept);
        $disable = $this->conceptsService->disablePaymentConcept($domainConcept);

        return response()->json([
            'success' => true,
            'data' => ['concept'=>$disable],
            'message' => 'Concepto de pago deshabilitado correctamente.'
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/concepts/{id}/activate",
     *     summary="Habilitar un concepto de pago",
     *     tags={"Payment Concepts"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(
     *         response=200,
     *         description="Concepto activado correctamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(
     *                     property="concept",
     *                     ref="#/components/schemas/DomainPaymentConcept"
     *                 )
     *
     *             ),
     *             @OA\Property(property="message", type="string", example="Concepto de pago habilitado correctamente.")
     *         )
     *     ),
     *     @OA\Response(response=409, description="Conflicto en los datos")
     * )
     */
    public function activate(PaymentConcept $concept)
    {
        $domainConcept = InfraPaymentConceptMapper::toDomain($concept);
        $activate = $this->conceptsService->activatePaymentConcept($domainConcept);

        return response()->json([
            'success' => true,
            'data' => ['concept'=>$activate],
            'message' => 'Concepto de pago habilitado correctamente.'
        ]);
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/concepts/{id}",
     *     summary="Eliminar concepto de pago (físicamente)",
     *     tags={"Payment Concepts"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Concepto de pago eliminado correctamente")
     * )
     */
    public function eliminate(PaymentConcept $concept)
    {
        $domainConcept = InfraPaymentConceptMapper::toDomain($concept);
        $this->conceptsService->eliminatePaymentConcept($domainConcept);

        return response()->json([
            'success' => true,
            'message' => 'Concepto de pago eliminado correctamente.'
        ]);
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/concepts/{id}/logical",
     *     summary="Eliminar concepto de pago (lógicamente)",
     *     tags={"Payment Concepts"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Concepto eliminado correctamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(
     *                     property="concept",
     *                     ref="#/components/schemas/DomainPaymentConcept"
     *                 )
     *
     *             ),
     *             @OA\Property(property="message", type="string", example="Concepto de pago eliminado correctamente.")
     *         )
     *     ),
     *     @OA\Response(response=409, description="Conflicto en los datos")
     * )
     */
    public function eliminateLogical(PaymentConcept $concept)
    {
        $domainConcept = InfraPaymentConceptMapper::toDomain($concept);
        $eliminate = $this->conceptsService->eliminateLogicalPaymentConcept($domainConcept);

        return response()->json([
            'success' => true,
            'data' => ['concept'=>$eliminate],
            'message' => 'Concepto de pago eliminado correctamente.'
        ]);
    }
}
