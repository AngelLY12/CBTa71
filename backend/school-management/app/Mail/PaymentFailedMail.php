<?php

namespace App\Mail;

use App\Core\Application\DTO\Request\Mail\PaymentFailedEmailDTO;
use Illuminate\Bus\Queueable;
use MailerSend\Helpers\Builder\Personalization;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use MailerSend\LaravelDriver\MailerSendTrait;

class PaymentFailedMail extends Mailable
{
    use Queueable, SerializesModels, MailerSendTrait;

    protected PaymentFailedEmailDTO $data;


    /**
     * Create a new message instance.
     */
    public function __construct(PaymentFailedEmailDTO $data)
    {
        $this->data = $data;
    }

    public function build()
    {
       try {
        $personalization = [
            new Personalization($this->data->recipientEmail, [
                'greeting' => "Hola {$this->data->recipientName}",
                'error' => $this->data->error,
                'concept_name' => $this->data->concept_name ?? 'No disponible',
                'amount' => isset($this->data->amount) ? number_format($this->data->amount, 2) : '0.00',
            ])
        ];

        return $this->mailersend(
                     template_id:'351ndgwmzxnlzqx8',
                     personalization: $personalization
                 );

    } catch (\Throwable $e) {
        logger()->error('Fallo al construir mail: '.$e->getMessage(), ['trace' => $e->getTraceAsString()]);
        throw $e;
    }
    }

}
