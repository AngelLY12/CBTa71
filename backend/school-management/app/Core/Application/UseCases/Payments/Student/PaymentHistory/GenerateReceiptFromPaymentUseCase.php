<?php

namespace App\Core\Application\UseCases\Payments\Student\PaymentHistory;

use App\Core\Application\Services\Payments\Student\ReceiptPdfService;
use App\Core\Domain\Repositories\Command\Payments\ReceiptRepInterface;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class GenerateReceiptFromPaymentUseCase
{

    public function __construct(
        private ReceiptRepInterface $receiptRep,
        private ReceiptPdfService $receiptPdfService
    ){}

    public function execute(int $paymentId): array
    {
        $receipt = $this->receiptRep->getOrCreateReceipt($paymentId);
        if(!$receipt)
        {
            throw new ModelNotFoundException('No se encontro el recibo solicitado');
        }
        $path =$this->receiptPdfService->generate($receipt);
        return [
            'path' => $path,
            'filename' => "{$receipt->folio}.pdf"
        ];
    }

}
