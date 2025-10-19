<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use MailerSend\Helpers\Builder\Personalization;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use MailerSend\LaravelDriver\MailerSendTrait;

class PaymentFailedMail extends Mailable
{
    use Queueable, SerializesModels, MailerSendTrait;

    protected array $data;
    protected string $recipientName;
    protected string $recipientEmail;
    protected string $error;


    /**
     * Create a new message instance.
     */
    public function __construct(array $data, string $recipientName, string $recipientEmail, string $error)
    {
        $this->data = $data;
        $this->recipientName = $recipientName;
        $this->recipientEmail = $recipientEmail;
        $this->error=$error;
    }

    public function build()
    {
       try {
        $personalization = [
            new Personalization($this->recipientEmail, [
                'greeting' => "Hola {$this->recipientName}",
                'error' => $this->error,
                'concept_name' => $this->data['concept_name'] ?? 'No disponible',
                'amount' => isset($this->data['amount']) ? number_format($this->data['amount'], 2) : '0.00',
            ])
        ];

        return $this->mailersend(
                     template_id:'neqvygme2ez40p7w',
                     personalization: $personalization
                 );

    } catch (\Throwable $e) {
        logger()->error('Fallo al construir mail: '.$e->getMessage(), ['trace' => $e->getTraceAsString()]);
        throw $e;
    }
    }

}
