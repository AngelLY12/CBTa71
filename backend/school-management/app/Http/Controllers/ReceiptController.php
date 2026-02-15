<?php

namespace App\Http\Controllers;

use App\Models\Receipt;
use Illuminate\Http\Request;

class ReceiptController extends Controller
{
    public function verify($folio)
    {
        $receipt = Receipt::where('folio', $folio)->first();
        return view('receipts.verify', ['receipt' => $receipt]);
    }
}
