<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Notificación')</title>

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap');

        body {
            font-family: 'Poppins', Arial, sans-serif;
            background-color: #f0f2f5;
            margin: 0;
            padding: 0;
            color: #333;
        }

        .container {
            max-width: 600px;
            margin: 40px auto;
            background-color: #fff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 6px 25px rgba(0,0,0,0.08);
        }

        .header {
            background: linear-gradient(90deg, #4CA771, #013237);
            color: #fff;
            text-align: center;
            padding: 30px 20px;
        }

        .header h1 {
            margin: 0;
            font-size: 26px;
            font-weight: 700;
        }

        .content {
            padding: 30px 25px;
        }

        .content p {
            font-size: 16px;
            line-height: 1.6;
            margin: 12px 0;
        }

        .greeting {
            font-size: 16px;
            color: #013237;
            font-weight: 600;
        }

        .section {
            background-color: #f9fafb;
            border-left: 5px solid #013237;
            padding: 15px 20px;
            border-radius: 8px;
            margin: 15px 0;
        }

        .footer {
            text-align: center;
            font-size: 12px;
            color: #888;
            padding: 20px;
            background-color: #f0f2f5;
        }

        a {
            color: #4CA771;
            text-decoration: none;
        }
    </style>
</head>

<body>
<div class="container">
    <div class="header" style="background: linear-gradient(90deg, #4CA771, #013237); text-align: center; padding: 30px 20px; color: #fff;">
        <img
            src="{{ $message->embed(public_path('Logo-cbta.webp')) }}"
            alt="Logo CBTA"
            style="max-width: 120px; width: 40%; height: auto; display: block; margin: 0 auto 15px auto;"
        >

        <h1 style="margin: 0; font-size: 26px; font-weight: 700; line-height: 1.2;">
            @yield('header_title')
        </h1>
    </div>

    <div class="content">
        <p class="greeting">@yield('greeting')</p>

        <p>@yield('message_intro')</p>

        <div class="section">
            @yield('message_details')
        </div>

        <p>@yield('message_footer')</p>
    </div>

    <div class="footer">
        Este es un mensaje automático. Por favor, no respondas a este correo.
    </div>
</div>
</body>
</html>
