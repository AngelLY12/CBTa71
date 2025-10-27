<?php

namespace App\Mail;

use App\Core\Application\DTO\Request\Mail\PaymentValidatedEmailDTO;
use Illuminate\Bus\Queueable;
use MailerSend\Helpers\Builder\Personalization;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use MailerSend\LaravelDriver\MailerSendTrait;

class PaymentValidatedMail extends Mailable
{
    use Queueable, SerializesModels, MailerSendTrait;

    protected PaymentValidatedEmailDTO $data;

    /**
     * Create a new message instance.
     */
    public function __construct(PaymentValidatedEmailDTO $data)
    {
        $this->data = $data;
    }

    public function build()
    {
       try {

        $voucherNumber = $this->data->payment_method_detail['oxxo']['number'] ?? 'No aplica';
        $speiReference = $this->data->payment_method_detail['spei']['reference'] ?? 'No aplica';
        $type_payment_method = $this->data->payment_method_detail['type'] ?? 'Desconocido';

        $messageDetails = "
            <p><strong>Concepto:</strong> {$this->data->concept_name}</p>
            <p><strong>Monto:</strong> $".number_format($this->data->amount, 2)."</p>
            <p><strong>Método de pago:</strong> {$this->data['type_payment_method']}</p>
            <p><strong>Código de referencia:</strong> {$this->data->payment_intent_id}</p>
            <p><strong>Voucher OXXO:</strong> {$voucherNumber}</p>
            <p><strong>Referencia SPEI:</strong> {$speiReference}</p>
            <p><strong>URL comprobante:</strong> <a href='{$this->data->url}' target='_blank'>{$this->data['url']}</a></p>
        ";

        $personalization = [
                new Personalization($this->data->recipientEmail, [
                    'greeting' => "Hola {$this->data->recipientName}",
                    'header_title' => 'Pago Validado',
                    'message_intro' => 'Tu pago ha sido validado exitosamente.',
                    'message_details' => $messageDetails,
                    'message_footer' => 'Gracias por realizar tu pago a tiempo.',
                ])
            ];

        return $this->mailersend(
                     template_id:'vywj2lp72zml7oqz',
                     personalization: $personalization
                 );

    } catch (\Throwable $e) {
        logger()->error('Fallo al construir mail: '.$e->getMessage(), ['trace' => $e->getTraceAsString()]);
        throw $e;
    }
    }

}
