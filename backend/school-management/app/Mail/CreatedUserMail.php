<?php

namespace App\Mail;

use App\Core\Application\DTO\Request\Mail\NewUserCreatedEmailDTO;
use App\Core\Application\DTO\Request\User\AdminCreateUserDTO;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use MailerSend\Helpers\Builder\Personalization;
use MailerSend\LaravelDriver\MailerSendTrait;

class CreatedUserMail extends Mailable
{
    use Queueable, SerializesModels, MailerSendTrait;

    protected NewUserCreatedEmailDTO $data;

    /**
     * Create a new message instance.
     */
    public function __construct(NewUserCreatedEmailDTO $data)
    {
        $this->data = $data;
    }

    public function build()
    {
       try {
        $messageDetails = "
                <p><strong>Tu contraseña es:</strong> {$this->data->password}</p>
            ";

        $personalization = [
            new Personalization($this->data->recipientEmail, [
                'greeting' => "Hola {$this->data->recipientName}",
                'header_title' => 'Cuenta creada',
                'message_intro' => 'Hemos creado una cuenta para ti.',
                'message_details' => $messageDetails,
                'message_footer' => 'Recuerda cambiar tu contraseña de ser posible.',
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
