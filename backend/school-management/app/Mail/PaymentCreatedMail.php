<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use MailerSend\Helpers\Builder\Personalization;
use Illuminate\Queue\SerializesModels;
use MailerSend\LaravelDriver\MailerSendTrait;

class PaymentCreatedMail extends Mailable
{
    use Queueable, SerializesModels, MailerSendTrait;

    protected array $data;
    protected string $recipientName;
    protected string $recipientEmail;

    /**
     * Create a new message instance.
     */
    public function __construct(array $data, string $recipientName, string $recipientEmail)
    {
        $this->data = $data;
        $this->recipientName = $recipientName;
        $this->recipientEmail = $recipientEmail;
    }

    public function build()
    {
       try {
        $messageDetails = "
                <p><strong>Concepto:</strong> {$this->data['concept_name']}</p>
                <p><strong>Monto:</strong> $".number_format($this->data['amount'], 2)."</p>
                <p><strong>Fecha de pago:</strong> {$this->data['created_at']}</p>
                <p><strong>Sesión de pago:</strong> {$this->data['stripe_session_id']}</p>
                <p><strong>URL de la sesión:</strong> <a href='{$this->data['url']}' target='_blank'>{$this->data['url']}</a></p>
            ";
        $personalization = [
            new Personalization($this->recipientEmail, [
                'greeting' => "Hola {$this->recipientName}",
                'header_title' => 'Confirmación de pago',
                'message_intro' => 'Hemos recibido tu pago correctamente.',
                'message_details' => $messageDetails,
                'message_footer' => 'Gracias por tu puntualidad. Te avisaremos cuando haya sido validado tu pago.',
                'logo_url' => $this->data['logo_url'] ?? null,
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
