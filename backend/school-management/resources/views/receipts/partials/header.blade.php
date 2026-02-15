<div class="receipt-header">
    <div class="logo-section">
        <div class="logo-container">
            <!-- Engranajes -->
            <div class="gear gear-outer"></div>
            <div class="gear gear-middle"></div>
            <div class="gear gear-inner"></div>

            <!-- Nopal -->
            <div class="nopal">
                <div class="nopal-leaf leaf-left"></div>
                <div class="nopal-leaf leaf-right"></div>
                <div class="nopal-body"></div>
                <div class="nopal-spines"></div>
            </div>

            <!-- Fruto -->
            <div class="fruit"></div>
        </div>

        <div class="school-info">
            <div class="school-name">CBTA No. 71 TLALNEPANTLA</div>
            <div class="school-campus">MORELOS</div>
        </div>
    </div>

    <div class="receipt-title">
        <h1>RECIBO DE PAGO</h1>
        <p>Comprobante oficial de pago</p>
    </div>
</div>

<style>
    .receipt-header {
        background: linear-gradient(135deg, #4CA771, #013237);
        padding: clamp(15px, 4vw, 30px) clamp(15px, 4vw, 30px) clamp(12px, 3vw, 20px);
        color: white;
    }

    .logo-section {
        display: flex;
        align-items: center;
        gap: clamp(12px, 3vw, 20px);
        flex-wrap: wrap;
    }

    /* Logo container responsivo */
    .logo-container {
        width: clamp(70px, 15vw, 100px);
        height: clamp(70px, 15vw, 100px);
        border-radius: 50%;
        margin: 0 auto 0 0;
        background: radial-gradient(circle at 30% 30%, #1f5e4c, #0a2e2a 90%);
        border: clamp(3px, 0.8vw, 5px) solid #bfd8d1;
        box-shadow: 0 clamp(4px, 1vw, 6px) clamp(8px, 2vw, 14px) rgba(0,0,0,0.4);
        display: flex;
        align-items: center;
        justify-content: center;
        position: relative;
        flex-shrink: 0;
    }

    /* Engranajes responsivos */
    .gear {
        position: absolute;
        border-radius: 50%;
    }

    .gear-outer {
        width: clamp(63px, 13vw, 90px);
        height: clamp(63px, 13vw, 90px);
        border: clamp(1.5px, 0.3vw, 2px) dashed rgba(255,255,255,0.6);
    }

    .gear-middle {
        width: clamp(53px, 11vw, 76px);
        height: clamp(53px, 11vw, 76px);
        border: clamp(1.5px, 0.3vw, 2px) solid rgba(215,230,223,0.9);
    }

    .gear-inner {
        width: clamp(43px, 9vw, 62px);
        height: clamp(43px, 9vw, 62px);
        background: rgba(15,55,50,0.8);
        border: clamp(1.5px, 0.3vw, 2px) solid #aac9bf;
    }

    /* Nopal responsivo */
    .nopal {
        position: absolute;
        width: clamp(18px, 4vw, 26px);
        height: clamp(24px, 5vw, 34px);
        bottom: clamp(20px, 4vw, 30px);
        left: 0;
        right: 0;
        margin: auto;
    }

    .nopal-leaf {
        position: absolute;
        width: clamp(8px, 1.8vw, 12px);
        height: clamp(13px, 2.8vw, 18px);
        background: linear-gradient(180deg,#49a98d,#2d7f6b);
        border: clamp(1.5px, 0.3vw, 2px) solid #cfe6df;
        border-radius: 10px 10px 6px 10px;
    }

    .nopal-leaf.leaf-left {
        left: clamp(-6px, -1.2vw, -8px);
        bottom: clamp(6px, 1.2vw, 8px);
        border-right: none;
    }

    .nopal-leaf.leaf-right {
        right: clamp(-6px, -1.2vw, -8px);
        bottom: clamp(8px, 1.5vw, 10px);
        border-left: none;
        border-radius: 10px 10px 10px 6px;
    }

    .nopal-body {
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        margin: auto;
        width: clamp(13px, 2.8vw, 18px);
        height: clamp(24px, 5vw, 34px);
        background: linear-gradient(180deg,#5ec2a3,#2d7f6b);
        border: clamp(1.5px, 0.3vw, 2px) solid #e2f1ec;
        border-radius: 12px 12px 8px 8px;
        box-shadow: inset 0 0 clamp(4px, 0.8vw, 6px) rgba(0,0,0,0.25), 0 clamp(1.5px, 0.3vw, 2px) clamp(2px, 0.4vw, 3px) rgba(0,0,0,0.35);
    }

    .nopal-spines {
        position: absolute;
        bottom: clamp(2px, 0.5vw, 3px);
        left: 0;
        right: 0;
        margin: auto;
        width: clamp(1.5px, 0.3vw, 2px);
        height: clamp(20px, 4vw, 28px);
        background: rgba(255,255,255,0.35);
        border-radius: 2px;
        box-shadow: clamp(-4px, -0.8vw, -5px) 0 0 rgba(255,255,255,0.18),
        clamp(4px, 0.8vw, 5px) 0 0 rgba(255,255,255,0.18);
    }

    /* Fruto */
    .fruit {
        position: absolute;
        top: clamp(25px, 5vw, 35px);
        right: clamp(23px, 4.8vw, 32px);
        width: clamp(4px, 0.9vw, 6px);
        height: clamp(4px, 0.9vw, 6px);
        background: #ffb347;
        border-radius: 50%;
        box-shadow: 0 0 0 clamp(1.5px, 0.3vw, 2px) rgba(255,180,70,0.3);
    }

    /* Info de la escuela responsiva */
    .school-info {
        flex: 1;
        min-width: min(200px, 60%);
    }

    .school-name {
        font-size: clamp(16px, 3.5vw, 20px);
        font-weight: 700;
        letter-spacing: clamp(0.5px, 0.1vw, 1px);
        color: #e3fff5;
        text-shadow: 1px 1px 0 #0a2e2a;
        margin: 0 0 clamp(2px, 0.5vw, 4px);
        line-height: 1.3;
        word-break: break-word;
    }

    .school-campus {
        font-size: clamp(13px, 2.5vw, 16px);
        font-weight: 600;
        color: #d4f0e6;
        letter-spacing: clamp(1px, 0.2vw, 2px);
        text-transform: uppercase;
        border-top: 1px solid #6f9e92;
        display: inline-block;
        padding-top: clamp(3px, 0.6vw, 5px);
        margin: 0;
    }

    /* Título responsivo */
    .receipt-title {
        text-align: left;
        border-top: 2px solid rgba(255,255,255,0.2);
        padding-top: clamp(10px, 2vw, 15px);
        margin-top: clamp(8px, 1.5vw, 10px);
    }

    @media (min-width: 480px) {
        .receipt-title {
            text-align: right;
        }
    }

    .receipt-title h1 {
        font-size: clamp(20px, 5vw, 32px);
        font-weight: 700;
        margin: 0;
        color: white;
        text-transform: uppercase;
        letter-spacing: clamp(1px, 0.3vw, 2px);
        line-height: 1.2;
    }

    .receipt-title p {
        font-size: clamp(11px, 2.5vw, 14px);
        margin: clamp(3px, 0.8vw, 5px) 0 0;
        opacity: 0.9;
        color: #d4f0e6;
    }

    /* Ajuste para pantallas muy pequeñas */
    @media (max-width: 360px) {
        .logo-section {
            flex-direction: column;
            align-items: flex-start;
            gap: 8px;
        }

        .logo-container {
            margin: 0 auto;
        }

        .school-info {
            width: 100%;
            text-align: center;
        }
    }

    /* Ajuste para tablets */
    @media (min-width: 768px) and (max-width: 1024px) {
        .logo-container {
            width: 90px;
            height: 90px;
        }

        .school-name {
            font-size: 19px;
        }

        .receipt-title h1 {
            font-size: 28px;
        }
    }
</style>
