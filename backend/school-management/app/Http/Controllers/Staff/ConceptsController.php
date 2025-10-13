<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Services\PaymentSystem\Staff\ConceptsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\PaymentConcept;


class ConceptsController extends Controller
{
    protected ConceptsService $conceptsService;

    public function __construct(ConceptsService $conceptsService)
    {
        $this->conceptsService= $conceptsService;


    }

    public function index(Request $request)
    {
        $status = strtolower($request->input('status','todos'));
        $data = $this->conceptsService->showConcepts($status);
        return response()->json([
                'success' => true,
                'data' => $data,
                'message'=>$data->isEmpty() ? 'No hay conceptos de pago creados' : null
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
            'semestre',
            'career',
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
            'semestre'              =>'nullable|numeric',
            'career'                =>'nullable|string',
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
        $concept = new PaymentConcept([
                'concept_name' => $data['concept_name'],
                'description'  => $data['description'] ?? null,
                'status'       =>  strtolower($data['status']),
                'start_date'   => $data['start_date'],
                'end_date'     => $data['end_date'] ?? null,
                'amount'       => $data['amount'],
                'is_global'    => $data['is_global'],
            ]);
        $createdConcept=$this->conceptsService->createPaymentConcept(
            $concept,
            strtolower($data['applies_to'] ?? 'todos'),
            $data['semestre'] ?? null,
            $data['career'] ?? null,
            $data['students'] ?? []
        );

        return response()->json([
        'success' => true,
        'data' => $createdConcept,
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
            'semestre',
            'career',
            'students'
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
            'semestre'      => 'nullable|numeric',
            'career'        => 'nullable|string',
            'students'      => 'nullable|array',
        ];

        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors(),
                'message' => 'Error en la validación de datos.'
            ], 422);
        }

        $updatedConcept = $this->conceptsService->updatePaymentConcept(
            $concept,
            $data,
            $data['semestre'] ?? null,
            $data['career'] ?? null,
            $data['students'] ?? null
        );

        return response()->json([
            'success' => true,
            'data' => $updatedConcept,
            'message' => 'Concepto de pago actualizado correctamente.'
        ]);
    }

    /**
     * Finalizar un concepto de pago.
     */
    public function finalize(PaymentConcept $concept)
    {
        $finalized = $this->conceptsService->finalizePaymentConcept($concept);

        return response()->json([
            'success' => true,
            'data' => $finalized,
            'message' => 'Concepto de pago finalizado correctamente.'
        ]);
    }
    public function disable(PaymentConcept $concept)
    {
        $disable = $this->conceptsService->disablePaymentConcept($concept);

        return response()->json([
            'success' => true,
            'data' => $disable,
            'message' => 'Concepto de pago deshabilitado correctamente.'
        ]);
    }

    public function eliminate(PaymentConcept $concept)
    {
        $eliminate = $this->conceptsService->eliminatePaymentConcept($concept);

        return response()->json([
            'success' => true,
            'data' => $eliminate,
            'message' => 'Concepto de pago eliminado correctamente.'
        ]);
    }
}
