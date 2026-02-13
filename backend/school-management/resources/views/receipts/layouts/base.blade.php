<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Recibo de Pago - CBTA No. 71')</title>
    <style>

        body {
            font-family: DejaVu Sans, sans-serif;
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

    </style>
    @stack('styles')
</head>
<body>
<div class="receipt-container">
    @include('receipts.partials.header')

    <div class="receipt-body watermark">
        @yield('content')

        @include('receipts.partials.footer')
    </div>
</div>

@stack('scripts')
</body>
</html>
