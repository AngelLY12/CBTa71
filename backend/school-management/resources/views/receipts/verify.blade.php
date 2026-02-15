<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verificar Recibo - CBTA No. 71</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #f0f2f5 0%, #e0e7e3 100%);
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .verification-card {
            max-width: 500px;
            width: 100%;
            background: white;
            border-radius: 24px;
            overflow: hidden;
            box-shadow: 0 20px 40px rgba(1, 50, 55, 0.2);
            animation: slideUp 0.5s ease;
        }

        .header {
            background: linear-gradient(135deg, #013237 0%, #1a4d44 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }

        .header h1 {
            margin: 0;
            font-size: 28px;
            font-weight: 600;
        }

        .header p {
            margin: 10px 0 0;
            opacity: 0.9;
            font-size: 14px;
        }

        .content {
            padding: 30px;
        }

        .status-badge {
            text-align: center;
            padding: 20px;
            border-radius: 16px;
            margin-bottom: 25px;
        }

        .status-badge.valid {
            background: #d4edda;
            color: #155724;
            border: 2px solid #28a745;
        }

        .status-badge.invalid {
            background: #f8d7da;
            color: #721c24;
            border: 2px solid #dc3545;
        }

        .status-badge h2 {
            margin: 0 0 10px;
            font-size: 24px;
        }

        .status-badge p {
            margin: 0;
            font-size: 16px;
        }

        .receipt-data {
            background: #f8fcfb;
            border-radius: 16px;
            padding: 20px;
            border: 1px solid #d0e6de;
        }

        .data-row {
            display: flex;
            justify-content: space-between;
            padding: 12px 0;
            border-bottom: 1px solid #e0e7e3;
        }

        .data-row:last-child {
            border-bottom: none;
        }

        .data-label {
            color: #4CA771;
            font-weight: 600;
            font-size: 14px;
            text-transform: uppercase;
        }

        .data-value {
            color: #013237;
            font-weight: 500;
            text-align: right;
        }

        .data-value.strong {
            font-weight: 700;
            font-size: 18px;
        }

        .footer {
            text-align: center;
            padding: 20px 30px;
            background: #f0f7f4;
            border-top: 1px solid #d0e6de;
        }

        .footer small {
            color: #4a6b63;
            font-size: 12px;
        }

        .verification-date {
            text-align: center;
            margin-top: 20px;
            color: #666;
            font-size: 12px;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @media print {
            body { background: white; }
            .verification-card { box-shadow: none; }
        }
    </style>
</head>
<body>
<div class="verification-card">
    <div class="header">
        <h1>CBTA No. 71</h1>
        <p>Verificación de Recibo</p>
    </div>

    <div class="content">
        @if($receipt)
            <div class="status-badge valid">
                <h2>✓ RECIBO VÁLIDO</h2>
                <p>Este recibo existe en nuestros registros oficiales</p>
            </div>

            <div class="receipt-data">
                <div class="data-row">
                    <span class="data-label">Folio</span>
                    <span class="data-value strong">{{ $receipt->folio }}</span>
                </div>

                <div class="data-row">
                    <span class="data-label">Alumno</span>
                    <span class="data-value">{{ $receipt->payer_name }}</span>
                </div>

                <div class="data-row">
                    <span class="data-label">Email</span>
                    <span class="data-value">{{ $receipt->payer_email }}</span>
                </div>

                <div class="data-row">
                    <span class="data-label">Concepto</span>
                    <span class="data-value">{{ $receipt->concept_name }}</span>
                </div>

                <div class="data-row">
                    <span class="data-label">Monto pagado</span>
                    <span class="data-value strong">${{ number_format($receipt->amount_received, 2) }} MXN</span>
                </div>

                <div class="data-row">
                    <span class="data-label">Fecha de emisión</span>
                    <span class="data-value">{{ $receipt->issued_at->format('d/m/Y H:i') }}</span>
                </div>

                @if($receipt->transaction_reference)
                    <div class="data-row">
                        <span class="data-label">Referencia</span>
                        <span class="data-value">{{ $receipt->transaction_reference }}</span>
                    </div>
                @endif
            </div>

            <div class="verification-date">
                Verificado el {{ now()->format('d/m/Y H:i:s') }}
            </div>
        @else
            <div class="status-badge invalid">
                <h2>✗ RECIBO NO VÁLIDO</h2>
                <p>El folio proporcionado no existe en nuestros registros</p>
            </div>
        @endif
    </div>

    <div class="footer">
        <small>
            Este documento es una verificación oficial<br>
            CBTA No. 71 - Todos los derechos reservados
        </small>
    </div>
</div>
</body>
</html>
