<?php

namespace App\Http\Controllers;

use App\Core\Application\Services\Payments\Student\PaymentHistoryService;


class ReceiptController extends Controller
{
    protected PaymentHistoryService $paymentHistoryService;
    public function __construct(PaymentHistoryService $paymentHistoryService){
        $this->paymentHistoryService = $paymentHistoryService;
    }
    public function verify($token)
    {
        try {
            $token = urldecode($token);
            $payload = json_decode(base64_decode($token), true);

            $expectedHash = hash_hmac('sha256', $payload['folio'], config('app.key'));

            if (!hash_equals($expectedHash, $payload['hash'])) {
                abort(403, 'QR inválido o modificado');
            }

            $receipt = $this->paymentHistoryService->validateReceipt($payload['folio']);
            return view('receipts.verify', ['receipt' => $receipt]);

        }catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            abort(404, 'Recibo no encontrado en la base de datos');
        } catch (\InvalidArgumentException $e) {
            abort(400, 'Token mal formado');
        }
        catch (\Exception $e) {
            abort(403, 'QR inválido');
        }
    }
}
