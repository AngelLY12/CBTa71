<?php

namespace App\Http\Controllers;

use App\Core\Application\Services\Misc\FindEntityServiceFacades;
use App\Http\Requests\General\ForceRefreshRequest;
use Illuminate\Support\Facades\Response;

/**
 * @OA\Tag(
 *     name="FindEntity",
 *     description="Operaciones para buscar usuarios, pagos y conceptos"
 * )
 */
class FindEntityController extends Controller
{
    private FindEntityServiceFacades $service;
    public function __construct(
        FindEntityServiceFacades $service
    )
    {
        $this->service=$service;
    }

    public function findConcept(int $id)
    {
        $concept=$this->service->findConcept($id);
        return Response::success(['concept' => $concept], 'Concepto encontrado.');

    }

    public function findPayment(int $id)
    {
        $payment=$this->service->findPayment($id);
        return Response::success(['payment' => $payment], 'Pago encontrado.');

    }

    public function findUser(ForceRefreshRequest $request)
    {
        $forceRefresh = $request->validated()['forceRefresh'] ?? false;
        $user=$this->service->findUser($forceRefresh);
        return Response::success(['user' => $user], 'Usuario encontrado.');

    }
}
