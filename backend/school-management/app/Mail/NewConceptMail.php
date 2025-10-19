<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use MailerSend\Helpers\Builder\Personalization;
use MailerSend\LaravelDriver\MailerSendTrait;

class NewConceptMail extends Mailable
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
        $personalization = [
            new Personalization($this->recipientEmail, [
                'amount' => number_format($this->data['amount'], 2),
                'end_date' => $this->data['end_date'] ?? 'Sin fecha límite',
                'greeting' => "Hola {$this->recipientName}",
                'concept_name' => $this->data['concept_name']
            ])
        ];

        return $this->mailersend(
                     template_id:'yzkq340odxk4d796',
                     personalization: $personalization
                 );

    } catch (\Throwable $e) {
        logger()->error('Fallo al construir mail: '.$e->getMessage(), ['trace' => $e->getTraceAsString()]);
        throw $e;
    }
    }


}
