<div class="receipt-header">
    @include('partials.logo')
    <div class="receipt-title">
        <h1>RECIBO DE PAGO</h1>
        <p>Comprobante oficial de pago</p>
    </div>
</div>

<style>
    :root {
        --color-primary: #013237;
        --color-secondary: #4CA771;
        --color-accent: #1a4d44;
        --color-gold: #ffb347;
        --logo-size: 100px;
        --logo-size-mobile: 70px;
        --logo-size-small: 60px;
    }

    .receipt-header {
        background: linear-gradient(135deg, var(--color-secondary), var(--color-primary));
        padding: clamp(20px, 5vw, 30px) clamp(16px, 4vw, 30px) clamp(16px, 3vw, 20px);
        color: white;
    }

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

    @media (min-width: 768px) and (max-width: 1024px) {

        .receipt-title h1 {
            font-size: 28px;
        }
    }

</style>
