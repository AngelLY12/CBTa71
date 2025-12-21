<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use MailerSend\Helpers\Builder\Personalization;
use MailerSend\LaravelDriver\MailerSendTrait;

class SendPasswordResetLinkMail extends Mailable
{
    use Queueable, SerializesModels, MailerSendTrait;

    protected $notifiable;
    protected $resetUrl;

    public function __construct($notifiable, $resetUrl)
    {
        $this->notifiable = $notifiable;
        $this->resetUrl = $resetUrl;
    }

    public function build()
    {
        $messageDetails = "
            <p><a href=\"{$this->resetUrl}\" target=\"_blank\">Verificar mi email</a></p>
            <p>Si no solicitaste restablecer la contraseña ignora este mensaje.</p>
        ";

        $personalization = [
            new Personalization($this->notifiable->email, [
                'greeting' => "Hola {$this->notifiable->name}",
                'header_title' => 'Recuperar contraseña',
                'message_intro' => 'Para restablecer tu contraseña debes ingresar al link.',
                'message_details' => $messageDetails,
                'message_footer' => 'Este enlace expirará en 60 minutos.',
            ])
        ];

        return $this->mailersend(
            template_id: 'pq3enl6d8z7g2vwr',
            personalization: $personalization
        );

    }

}
