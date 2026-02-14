<?php

namespace App\Core\Application\Services\Payments\Student;

use App\Models\Receipt;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class ReceiptPdfService
{
    public function generate(Receipt $receipt): string
    {
        $disk = Storage::disk('gcs');
        if ($receipt->file_path && $disk->exists($receipt->file_path)) {
            return $receipt->file_path;
        }

        $pdf = Pdf::loadView('receipts.receipt', [
            'receipt' => $receipt
        ]);

        $path = "receipts/". $receipt->issued_at->format('Y/m')."/{$receipt->folio}.pdf";

        $result=$disk->put($path, $pdf->output());

        if (!$result || !$disk->exists($path)) {
            throw new \Exception("No se pudo guardar el archivo ");
        }

        if ($receipt->file_path !== $path) {
            $receipt->update(['file_path' => $path]);
        }

        return $path;
    }

}
