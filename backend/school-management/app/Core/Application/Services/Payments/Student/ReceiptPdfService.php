<?php

namespace App\Core\Application\Services\Payments\Student;

use App\Models\Receipt;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class ReceiptPdfService
{
    public function generate(Receipt $receipt): string
    {
        if ($receipt->file_path) {
            return $receipt->file_path;
        }

        $pdf = Pdf::loadView('receipts.receipt', [
            'receipt' => $receipt
        ]);

        $path = "receipts/". $receipt->issued_at->format('Y/m')."/{$receipt->folio}.pdf";

        Storage::disk('gcs')->put($path, $pdf->output());

        $receipt->update([
            'file_path' => $path
        ]);

        return $path;
    }

}
