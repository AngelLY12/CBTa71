<div class="receipt-header">
    <div class="logo-section">
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
        ">
            <div style="position:absolute; width:90px; height:90px; border-radius:50%; border:2px dashed rgba(255,255,255,0.6);"></div>
            <div style="position:absolute; width:76px; height:76px; border-radius:50%; border:2px solid rgba(215,230,223,0.9);"></div>
            <div style="position:absolute; width:62px; height:62px; border-radius:50%; background:rgba(15,55,50,0.8); border:2px solid #aac9bf;"></div>

            <div style="position:absolute; width:26px; height:34px; bottom:30px; left:0; right:0; margin:auto;">
                <div style="position:absolute; left:-8px; bottom:8px; width:12px; height:18px; background:linear-gradient(180deg,#49a98d,#2d7f6b); border:2px solid #cfe6df; border-right:none; border-radius:10px 10px 6px 10px;"></div>
                <div style="position:absolute; right:-8px; bottom:10px; width:12px; height:20px; background:linear-gradient(180deg,#49a98d,#2d7f6b); border:2px solid #cfe6df; border-left:none; border-radius:10px 10px 10px 6px;"></div>
                <div style="position:absolute; bottom:0; left:0; right:0; margin:auto; width:18px; height:34px; background:linear-gradient(180deg,#5ec2a3,#2d7f6b); border:2px solid #e2f1ec; border-radius:12px 12px 8px 8px; box-shadow:inset 0 0 6px rgba(0,0,0,0.25), 0 2px 3px rgba(0,0,0,0.35);"></div>
                <div style="position:absolute; bottom:3px; left:0; right:0; margin:auto; width:2px; height:28px; background:rgba(255,255,255,0.35); border-radius:2px; box-shadow:-5px 0 0 rgba(255,255,255,0.18), 5px 0 0 rgba(255,255,255,0.18);"></div>
            </div>

            <div style="position:absolute; top:35px; right:32px; width:6px; height:6px; background:#ffb347; border-radius:50%; box-shadow:0 0 0 2px rgba(255,180,70,0.3);"></div>
        </div>

        <div class="school-info">
            <div class="school-name">CBTA No. 71 TLALNEPANTLA</div>
            <div class="school-campus">MORELOS</div>
        </div>
    </div>

    <div class="receipt-title">
        <h1>@yield('receipt_title', 'RECIBO DE PAGO')</h1>
        <p>@yield('receipt_subtitle', 'Comprobante oficial de pago')</p>
    </div>
</div>
