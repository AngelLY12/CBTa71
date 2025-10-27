<?php

namespace App\Http\Controllers\Staff;

use App\Core\Application\Mappers\PaymentConceptMapper;
use App\Core\Infraestructure\Mappers\PaymentConceptMapper as InfraPaymentConceptMapper;
use App\Core\Application\Services\Payments\Staff\ConceptsServiceFacades;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\PaymentConcept;


class ConceptsController extends Controller
{
    protected ConceptsServiceFacades $conceptsService;

    public function __construct(ConceptsServiceFacades $conceptsService)
    {
        $this->conceptsService= $conceptsService;


    }

    public function index(Request $request)
    {
        $status = strtolower($request->input('status','todos'));
        $data = $this->conceptsService->showConcepts($status);
        return response()->json([
                'success' => true,
                'data' => ['concepts'=>$data],
                'message'=>empty($data) ? 'No hay conceptos de pago creados' : null
            ]);

    }

    /**
     * Store a newly created resource in storage.
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
     * Finalizar un concepto de pago.
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

    public function eliminate(PaymentConcept $concept)
    {
        $domainConcept = InfraPaymentConceptMapper::toDomain($concept);
        $this->conceptsService->eliminatePaymentConcept($domainConcept);

        return response()->json([
            'success' => true,
            'message' => 'Concepto de pago eliminado correctamente.'
        ]);
    }

    public function eliminateLogical(PaymentConcept $concept)
    {
        $domainConcept = InfraPaymentConceptMapper::toDomain($concept);
        $eliminate = $this->conceptsService->elminateLogicalPaymentConcept($domainConcept);

        return response()->json([
            'success' => true,
            'data' => ['concept'=>$eliminate],
            'message' => 'Concepto de pago eliminado correctamente.'
        ]);
    }
}
