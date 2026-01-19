<?php

namespace App\Mail;

use App\Core\Domain\Utils\Helpers\Money;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use MailerSend\Helpers\Builder\Personalization;
use MailerSend\LaravelDriver\MailerSendTrait;

class CriticalAmountAlertMail extends Mailable
{
    use Queueable, SerializesModels, MailerSendTrait;
    public string $amount;
    public int $id;
    public string $email;
    public string $concept_name;
    public string $fullName;
    public string $threshold;
    public string $exceededBy;
    public string $action;
    /**
     * Create a new message instance.
     */
    public function __construct(string $amount,
                                int $id,
                                string $concept_name,
                                string $fullName,
                                string $email,
                                string $threshold,
                                string $exceededBy,
    string $action)
    {
        $this->amount = $amount;
        $this->id = $id;
        $this->concept_name = $concept_name;
        $this->fullName = $fullName;
        $this->email = $email;
        $this->threshold = $threshold;
        $this->exceededBy = $exceededBy;
        $this->action = $action;
    }

    /**
     * Get the message envelope.
     */
    public function build()
    {
        try {

            $messageDetails = "
            <p>Se {$this->action} un concepto con la siguiente información:</p>
            <p><strong>ID del Concepto:</strong> {$this->id}</p>
            <p><strong>Concepto:</strong> {$this->concept_name}</p>
            <p><strong>Monto:</strong> $".Money::from($this->amount)->finalize()."</p>
            <p><strong>Monto umbral: $".$this->threshold." </strong></p>
            <p><strong>Se excedio por:</strong> {$this->exceededBy}</p>

        ";

            $personalization = [
                new Personalization($this->email, [
                    'greeting' => "Hola {$this->fullName}",
                    'header_title' => config('concepts.amount.notifications.mail.title'),
                    'message_intro' =>  config('concepts.amount.notifications.mail.intro'),
                    'message_details' => $messageDetails,
                    'message_footer' => 'Asegurante de verificar este concepto, si no es un error ignora el correo.',
                ])
            ];

            return $this->mailersend(
                template_id:'pq3enl6d8z7g2vwr',
                personalization: $personalization
            );

        } catch (\Throwable $e) {
            logger()->error('Fallo al construir mail: '.$e->getMessage(), ['trace' => $e->getTraceAsString()]);
            throw $e;
        }
    }
}
