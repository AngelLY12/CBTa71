<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use MailerSend\Helpers\Builder\Personalization;
use Illuminate\Queue\SerializesModels;
use MailerSend\LaravelDriver\MailerSendTrait;

class RequiresActionMail extends Mailable
{
    use Queueable, SerializesModels, MailerSendTrait;

    protected array $data;
    protected string $recipientName;
    protected string $recipientEmail;
    protected string $type;

    public function __construct(array $data, string $recipientName, string $recipientEmail, string $type)
    {
        $this->data = $data;
        $this->recipientName = $recipientName;
        $this->recipientEmail = $recipientEmail;
        $this->type = $type;
    }

    public function build()
    {
       try {
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

            $personalization = [
                new Personalization($this->recipientEmail, [
                    'greeting' => "Hola {$this->recipientName}",
                    'header_title' => $headerTitle,
                    'message_intro' => $messageIntro,
                    'message_details' => $messageDetails,
                    'logo_url' => $this->data['logo_url'] ?? null,
                ])
            ];

            return $this->mailersend(
                template_id: 'vywj2lp72zml7oqz',
                personalization: $personalization
            );

        } catch (\Throwable $e) {
            logger()->error("Error enviando correo a {$this->recipientEmail}: {$e->getMessage()}");
            throw $e;
        }
    }

}
