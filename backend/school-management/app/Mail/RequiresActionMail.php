<?php

namespace App\Mail;

use App\Core\Application\DTO\Request\Mail\RequiresActionEmailDTO;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use MailerSend\Helpers\Builder\Personalization;
use Illuminate\Queue\SerializesModels;
use MailerSend\LaravelDriver\MailerSendTrait;

class RequiresActionMail extends Mailable
{
    use Queueable, SerializesModels, MailerSendTrait;

    protected RequiresActionEmailDTO $data;

    public function __construct(RequiresActionEmailDTO $data)
    {
        $this->data = $data;
    }

    public function build()
    {
       try {
        $url=null;
        $reference=null;
        $amount = bcdiv((string) $this->data->amount, '100', 2);
           if (isset($this->data->payment_method_options['oxxo'])) {
                $reference=$this->data->next_action['oxxo_display_details']['number'];
                $url=$this->data->next_action['oxxo_display_details']['hosted_voucher_url'];
                $expirationDays=$this->data->payment_method_options['oxxo']['expires_after_days'];
                $headerTitle = 'Instrucciones para completar tu pago en OXXO';
                $messageIntro = 'Para completar tu pago, acude a cualquier tienda OXXO y presenta el código de referencia en el voucher:';
                $messageDetails = "
                    <p><strong>Monto:</strong> $" . number_format($amount, 2) . "</p>
                    <p><strong>Número de referencia:</strong> {$reference}</p>
                    <p><strong>Voucher:</strong> <a href='{$url}' target='_blank'>Ver voucher</a></p>
                    <p>Tu pago será actualizado automáticamente una vez que completes la operación en la tienda OXXO.</p>
                    <p>Tienes un tiempo limite de {$expirationDays} para realizar el pago</p>
                ";
            } else {
                $url=$this->data->next_action['display_bank_transfer_instructions']['hosted_instructions_url'];
                $reference=$this->data->next_action['display_bank_transfer_instructions']['reference'];
                $headerTitle = 'Instrucciones para completar tu pago por transferencia bancaria';
                $messageIntro = 'Para completar tu pago, realiza una transferencia bancaria utilizando los siguientes datos:';
                $messageDetails = "
                    <p><strong>Monto:</strong> $" . number_format($amount, 2) . "</p>
                    <p><strong>Referencia:</strong> {$reference}</p>
                    <p><strong>Instrucciones:</strong> <a href='{$url}' target='_blank'>Ver instrucciones</a></p>
                    <p>Tu pago será actualizado automáticamente una vez que la transferencia sea recibida.</p>
                ";
            }

            $personalization = [
                new Personalization($this->data->recipientEmail, [
                    'greeting' => "Hola {$this->data->recipientName}",
                    'header_title' => $headerTitle,
                    'message_intro' => $messageIntro,
                    'message_details' => $messageDetails,
                ])
            ];

            return $this->mailersend(
                template_id: 'pq3enl6d8z7g2vwr',
                personalization: $personalization
            );

        } catch (\Throwable $e) {
            logger()->error("Error enviando correo a {$this->data->recipientEmail}: {$e->getMessage()}");
            throw $e;
        }
    }

}
