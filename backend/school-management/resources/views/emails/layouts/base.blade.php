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
        <div style="margin:0 auto 18px auto; text-align:center;">

            <div style="
                width:100px;
                height:100px;
                border-radius:50%;
                margin:0 auto 12px auto;
                background: radial-gradient(circle at 30% 30%, #1f5e4c, #0a2e2a 90%);
                border:5px solid #bfd8d1;
                box-shadow:0 6px 14px rgba(0,0,0,0.4);
                display:flex;
                align-items:center;
                justify-content:center;
                position:relative;
                color:white;
                font-size:38px;
            ">
                <div style="position:absolute; width:90px; height:90px; border-radius:50%; border:2px dashed rgba(255,255,255,0.6); top:0; left:0; right:0; bottom:0; margin:auto;"></div>

                <div style="position:absolute; width:76px; height:76px; border-radius:50%; border:2px solid rgba(215,230,223,0.9); top:0; left:0; right:0; bottom:0; margin:auto;"></div>

                <div style="position:absolute; width:62px; height:62px; border-radius:50%; background:rgba(15,55,50,0.8); border:2px solid #aac9bf; top:0; left:0; right:0; bottom:0; margin:auto;"></div>

                <div style="
                    position:absolute;
                    width:26px;
                    height:34px;
                    bottom:30px;
                    left:0;
                    right:0;
                    margin:auto;
                ">

                    <div style="
                        position:absolute;
                        left:-8px;
                        bottom:8px;
                        width:12px;
                        height:18px;
                        background:linear-gradient(180deg,#49a98d,#2d7f6b);
                        border:2px solid #cfe6df;
                        border-right:none;
                        border-radius:10px 10px 6px 10px;
                    "></div>

                    <div style="
                        position:absolute;
                        right:-8px;
                        bottom:10px;
                        width:12px;
                        height:20px;
                        background:linear-gradient(180deg,#49a98d,#2d7f6b);
                        border:2px solid #cfe6df;
                        border-left:none;
                        border-radius:10px 10px 10px 6px;
                    "></div>

                    <div style="
                        position:absolute;
                        bottom:0;
                        left:0;
                        right:0;
                        margin:auto;
                        width:18px;
                        height:34px;
                        background:linear-gradient(180deg,#5ec2a3,#2d7f6b);
                        border:2px solid #e2f1ec;
                        border-radius:12px 12px 8px 8px;
                        box-shadow:
                            inset 0 0 6px rgba(0,0,0,0.25),
                            0 2px 3px rgba(0,0,0,0.35);
                    "></div>

                    <div style="
                        position:absolute;
                        bottom:3px;
                        left:0;
                        right:0;
                        margin:auto;
                        width:2px;
                        height:28px;
                        background:rgba(255,255,255,0.35);
                        border-radius:2px;
                        box-shadow:
                            -5px 0 0 rgba(255,255,255,0.18),
                             5px 0 0 rgba(255,255,255,0.18);
                    "></div>

                </div>

                <div style="
                    position: absolute;
                    top: 35px;
                    right: 32px;
                    width: 6px;
                    height: 6px;
                    background: #ffb347;
                    border-radius: 50%;
                    box-shadow: 0 0 0 2px rgba(255, 180, 70, 0.3);
                "></div>
            </div>


            <div style="
                font-family: Arial, Helvetica, sans-serif;
                font-size:16px;
                font-weight:800;
                letter-spacing:2px;
                color:#e3fff5;
                text-transform:uppercase;
                text-shadow: 1px 1px 0 #0a2e2a;
                margin-top:6px;
            ">
                CBTA No. 71 TLALNEPANTLA
            </div>

            <!-- Localidad: Morelos, con estilo más destacado -->
            <div style="
                font-family: Arial, Helvetica, sans-serif;
                font-size:14px;
                font-weight:600;
                color:#d4f0e6;
                margin-top:4px;
                letter-spacing:2.5px;
                text-transform:uppercase;
                border-top:1px solid #6f9e92;
                display:inline-block;
                padding-top:5px;
                padding-left:12px;
                padding-right:12px;
            ">
                MORELOS
            </div>

        </div>

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
