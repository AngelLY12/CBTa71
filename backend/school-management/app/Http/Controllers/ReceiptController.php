<?php

namespace App\Http\Controllers;

use App\Models\Receipt;
use Illuminate\Http\Request;

class ReceiptController extends Controller
{
    public function verify($token)
    {
        try {
            $token = urldecode($token);
            $payload = json_decode(base64_decode($token), true);

            $expectedHash = hash_hmac('sha256', $payload['folio'], config('app.key'));

            if (!hash_equals($expectedHash, $payload['hash'])) {
                abort(403, 'QR inválido o modificado');
            }

            $receipt = Receipt::where('folio', $payload['folio'])->firstOrFail();

            return view('receipts.verify', ['receipt' => $receipt]);

        } catch (\Exception $e) {
            abort(403, 'QR inválido');
        }
    }
}
