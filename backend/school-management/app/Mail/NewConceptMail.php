<?php

namespace App\Mail;

use App\Core\Application\DTO\Request\Mail\NewPaymentConceptEmailDTO;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use MailerSend\Helpers\Builder\Personalization;
use MailerSend\LaravelDriver\MailerSendTrait;

class NewConceptMail extends Mailable
{
    use Queueable, SerializesModels, MailerSendTrait;

    protected NewPaymentConceptEmailDTO $data;
    /**
     * Create a new message instance.
     */
    public function __construct(NewPaymentConceptEmailDTO $data)
    {
        $this->data = $data;

    }

    public function build()
    {
       try {
        $personalization = [
            new Personalization($this->data->recipientEmail, [
                'amount' => number_format($this->data->amount, 2),
                'end_date' => $this->data->end_date ?? 'Sin fecha límite',
                'greeting' => "Hola {$this->data->recipientName}",
                'concept_name' => $this->data->concept_name
            ])
        ];

        return $this->mailersend(
                     template_id:'o65qngkm0n8lwr12',
                     personalization: $personalization
                 );

    } catch (\Throwable $e) {
        logger()->error('Fallo al construir mail: '.$e->getMessage(), ['trace' => $e->getTraceAsString()]);
        throw $e;
    }
    }
}
