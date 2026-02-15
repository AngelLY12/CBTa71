<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Recibo de Pago - CBTA No. 71')</title>
    <meta name="receipt-id" content="@yield('receipt_id', '')">
    <meta name="receipt-folio" content="@yield('folio', '')">
    <meta name="receipt-amount" content="@yield('amount', '')">

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>

        body {
            font-family: 'Poppins', Arial, sans-serif;
            background-color: #f0f2f5;
            margin: 0;
            padding: 20px;
            color: #333;
        }

        .receipt-container {
            max-width: 700px;
            margin: 0 auto;
            background-color: #fff;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(1, 50, 55, 0.15);
        }

        .receipt-body {
            padding: 35px 30px;
            position: relative;
            overflow: hidden;
        }

        .folio-section {
            background: linear-gradient(135deg, #f0f9f5, #e6f0ec);
            border-radius: 12px;
            padding: 15px 20px;
            margin-bottom: 25px;
            border: 1px solid #4CA771;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .folio-label {
            font-size: 14px;
            color: #013237;
            font-weight: 600;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }

        .folio-value {
            font-size: 20px;
            color: #013237;
            font-weight: 700;
            background: white;
            padding: 8px 20px;
            border-radius: 30px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
            font-family: monospace;
            letter-spacing: 1px;
        }

        .student-card {
            background: linear-gradient(to right, #f8fcfc, #f0f7f4);
            border-left: 6px solid #4CA771;
            border-radius: 12px;
            padding: 20px 25px;
            margin-bottom: 30px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.02);
        }

        .student-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }

        .student-label {
            font-size: 14px;
            color: #4CA771;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin: 0;
        }

        .payment-method-badge {
            background: #e8f4f0;
            border-radius: 30px;
            padding: 8px 16px;
            display: inline-block;
            border: 1px solid #4CA771;
            font-weight: 600;
            color: #013237;
            font-size: 14px;
        }

        .student-name {
            font-size: 24px;
            font-weight: 700;
            color: #013237;
            margin: 0 0 5px;
        }

        .student-email {
            color: #4CA771;
            font-size: 14px;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .details-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 25px;
            margin-bottom: 30px;
            background: #f9fcfb;
            padding: 20px;
            border-radius: 12px;
        }

        .detail-item {
            border-bottom: 1px solid #d0e6de;
            padding-bottom: 12px;
        }

        .detail-label {
            font-size: 12px;
            color: #4CA771;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin: 0 0 6px;
        }

        .detail-value {
            font-size: 16px;
            color: #013237;
            font-weight: 500;
            margin: 0;
            word-break: break-word;
        }

        .detail-value.strong {
            font-weight: 700;
            font-size: 18px;
        }

        .amount-box {
            background: linear-gradient(135deg, #013237, #1a4d44);
            border-radius: 16px;
            padding: 25px;
            margin: 20px 0 25px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 8px 20px rgba(1, 50, 55, 0.3);
        }

        .amount-label {
            color: #d4f0e6;
            font-size: 16px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin: 0;
        }

        .amount-value {
            color: white;
            font-size: 42px;
            font-weight: 700;
            margin: 0;
            text-shadow: 2px 2px 0 rgba(0,0,0,0.1);
        }

        .amount-value span {
            font-size: 24px;
            font-weight: 500;
            opacity: 0.9;
        }

        .reference-box {
            background: #f0f7f4;
            border-radius: 8px;
            padding: 12px 15px;
            margin-top: 15px;
            border: 1px dashed #4CA771;
        }

        .reference-label {
            font-size: 11px;
            color: #4CA771;
            font-weight: 600;
            text-transform: uppercase;
            margin-bottom: 5px;
        }

        .reference-value {
            font-size: 13px;
            color: #013237;
            font-family: monospace;
            word-break: break-all;
        }

        .payment-details-card {
            background: #f9fcfb;
            border-radius: 12px;
            padding: 20px;
            margin-top: 20px;
            border: 1px solid #d0e6de;
        }

        .payment-details-title {
            font-size: 14px;
            color: #4CA771;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 15px;
            border-bottom: 1px solid #d0e6de;
            padding-bottom: 8px;
        }

        .payment-details-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
        }

        .payment-detail-item {
            font-size: 13px;
        }

        .payment-detail-item .label {
            color: #4CA771;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 11px;
            display: block;
            margin-bottom: 3px;
        }

        .payment-detail-item .value {
            color: #013237;
            font-weight: 500;
            word-break: break-word;
        }

        .stripe-link {
            display: inline-block;
            margin-top: 15px;
            color: #4CA771;
            text-decoration: none;
            font-weight: 600;
            font-size: 13px;
            padding: 8px 15px;
            background: #e8f4f0;
            border-radius: 30px;
            border: 1px solid #4CA771;
        }

        .stripe-link:hover {
            background: #d0e6de;
        }

        .footer-note {
            border-top: 2px solid #e9f0ed;
            padding: 25px 0 0;
            margin-top: 30px;
            text-align: center;
        }

        .footer-note p {
            color: #4a6b63;
            font-size: 13px;
            margin: 5px 0;
        }

        .watermark {
            position: relative;
            overflow: hidden;
        }

        .watermark::after {
            content: "CBTA 71";
            position: absolute;
            bottom: 50px;
            right: 30px;
            font-size: 60px;
            font-weight: 800;
            color: rgba(76, 167, 113, 0.03);
            transform: rotate(-15deg);
            pointer-events: none;
            z-index: 0;
        }
        @media print {

            @page {
                size: A4;
                margin: 12mm;
            }

            body {
                background: white !important;
                padding: 0 !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            button {
                display: none !important;
            }

            .receipt-container {
                max-width: 100% !important;
                width: 100% !important;
                margin: 0 !important;
                border-radius: 0 !important;
                box-shadow: none !important;
                overflow: visible !important;
            }

            .receipt-header,
            .student-card,
            .details-grid,
            .amount-box,
            .payment-details-card,
            .reference-box,
            .footer-note,
            .folio-section {
                page-break-inside: avoid !important;
                break-inside: avoid !important;
            }

            .details-grid,
            .payment-details-grid {
                display: block !important;
            }

            .detail-item,
            .payment-detail-item {
                margin-bottom: 10px;
            }

            .watermark::after {
                opacity: 0.06 !important;
            }

            .amount-value {
                font-size: 32px !important;
            }

            .footer-note {
                page-break-before: avoid !important;
            }
        }

    </style>
    @stack('styles')
</head>
<body>
<div class="receipt-container">
    @include('receipts.partials.header')

    <div class="receipt-body watermark">
        @yield('content')

        @include('receipts.partials.footer')
        @include('receipts.partials.qr')
    </div>
</div>

@include('receipts.partials.print-button')
@include('receipts.partials.script.protection')
@stack('scripts')
</body>
</html>
