<?php

namespace App\Core\Application\DTO\Request\Mail;

class RequiresActionEmailDTO
{
    public function __construct(
        public readonly string $recipientName,
        public readonly string $recipientEmail,
        public readonly string $concept_name,
        public readonly int $amount,
        public readonly array $next_action,
        public readonly array $payment_method_options
    )
    {

    }

}

            if ($this->type === 'oxxo') {
                $headerTitle = 'Instrucciones para completar tu pago en OXXO';
                $messageIntro = 'Para completar tu pago, acude a cualquier tienda OXXO y presenta el código de referencia en el voucher:';
                $messageDetails = "
                    <p><strong>Monto:</strong> $" . number_format($this->data['amount'] / 100, 2) . "</p>
                    <p><strong>Número de referencia:</strong> {$this->data['reference_number']}</p>
                    <p><strong>Voucher:</strong> <a href='{$this->data['voucher']}' target='_blank'>Ver voucher</a></p>
                    <p>Tu pago será actualizado automáticamente una vez que completes la operación en la tienda OXXO.</p>
                ";
            } else {
                $headerTitle = 'Instrucciones para completar tu pago por transferencia bancaria';
                $messageIntro = 'Para completar tu pago, realiza una transferencia bancaria utilizando los siguientes datos:';
                $messageDetails = "
                    <p><strong>Monto:</strong> $" . number_format($this->data['amount'] / 100, 2) . "</p>
                    <p><strong>Referencia:</strong> {$this->data['reference_number']}</p>
                    <p><strong>Banco:</strong> " . ($this->data['bank_name'] ?? 'No disponible') . "</p>
                    <p><strong>CLABE:</strong> " . ($this->data['clabe'] ?? 'No disponible') . "</p>
                    <p><strong>Instrucciones:</strong> <a href='{$this->data['hosted_instructions_url']}' target='_blank'>Ver instrucciones</a></p>
                    <p>Tu pago será actualizado automáticamente una vez que la transferencia sea recibida.</p>
                ";
            }
