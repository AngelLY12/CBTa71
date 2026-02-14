<?php

namespace App\Http\Controllers\Students;

use App\Core\Application\Services\Payments\Student\PaymentHistoryService;
use App\Core\Infraestructure\Mappers\UserMapper;
use App\Http\Controllers\Controller;
use App\Http\Requests\General\ForceRefreshRequest;
use App\Http\Requests\General\PaginationRequest;
use App\Models\Payment;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;

/**
 * @OA\Tag(
 *     name="Payment history",
 *     description="Endpoints relacionados con el historial de pagos del usuario"
 * )
 */
class PaymentHistoryController extends Controller
{
    protected PaymentHistoryService $paymentHistoryService;
    public function __construct(PaymentHistoryService $paymentHistoryService){
        $this->paymentHistoryService= $paymentHistoryService;

    }


    public function index(PaginationRequest $request, ?int $studentId=null)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $forceRefresh = $request->validated()['forceRefresh'] ?? false;
        $targetUser = $user->resolveTargetUser($studentId);

        if (!$targetUser) {
            return Response::error('Acceso no permitido', 403);
        }
        $perPage = $request->integer('perPage', 15);
        $page = $request->integer('page', 1);
        $history=$this->paymentHistoryService->paymentHistory(UserMapper::toDomain($targetUser), $perPage, $page, $forceRefresh);
        return Response::success(
            ['payment_history' => $history],
            empty($history->items) ? 'No hay historial de pagos para este usuario.' : null
        );

    }

    public function findPayment(ForceRefreshRequest $request, int $id)
    {
        $forceRefresh = $request->validated()['forceRefresh'] ?? false;
        $payment=$this->paymentHistoryService->findPayment($id, $forceRefresh);
        return Response::success(['payment' => $payment], 'Pago encontrado.');

    }

    public function receiptPDF(int $paymentId)
    {
        $file = $this->paymentHistoryService->receiptFromPayment($paymentId);

        Log::info('DEBUG - receiptPDF', [
            'paymentId' => $paymentId,
            'path_del_service' => $file['path'],
            'filename' => $file['filename'],
            'path_completa' => $file['path'],
            'existe? (check manual)' => Storage::disk('gcs')->exists($file['path'])
        ]);

        if (!Storage::disk('gcs')->exists($file['path'])) {
            $directory = dirname($file['path']);
            $files = Storage::disk('gcs')->files($directory);

            Log::error('ARCHIVO NO ENCONTRADO', [
                'path_buscado' => $file['path'],
                'directorio' => $directory,
                'archivos_disponibles' => $files
            ]);

            return response()->json([
                'error' => 'Archivo no encontrado',
                'path_buscado' => $file['path'],
                'archivos_en_directorio' => $files
            ], 404);
        }

        return Storage::disk('gcs')->download(
            $file['path'],
            $file['filename'],
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="'.$file['filename'].'"',
                'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
            ]
        );
    }
}
