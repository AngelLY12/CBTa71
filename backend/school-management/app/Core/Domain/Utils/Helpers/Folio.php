<?php

namespace App\Core\Domain\Utils\Helpers;

use App\Models\Payment;
use Illuminate\Support\Str;

class Folio
{

    public static function generateReceiptFolio(Payment $payment): string
    {
        $conceptCode = Str::of($payment->concept_name)
            ->initials()
            ->upper()
            ->substr(0, 3);
        $conceptCode = str_pad($conceptCode, 3, 'X', STR_PAD_RIGHT);
        $dateCode = now()->format('mY');
        $correlative = str_pad($payment->id,10,'0',STR_PAD_LEFT);
        return "REC-{$conceptCode}-{$dateCode}-{$correlative}";
    }

}
