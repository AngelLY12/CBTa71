<?php

namespace App\Mail;

use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use MailerSend\Helpers\Builder\Personalization;
use MailerSend\LaravelDriver\MailerSendTrait;

class SendVerifyEmail extends Mailable
{
    use Queueable, SerializesModels, MailerSendTrait;

    protected $notifiable;
    protected $verifyUrl;

    public function __construct($notifiable, $verifyUrl)
    {
        $this->notifiable = $notifiable;
        $this->verifyUrl = $verifyUrl;
    }

    public function build()
    {

        $messageDetails = "
            <p>Para verificar tu correo, haz clic en el siguiente enlace:</p>
            <p><a href=\"{$this->verifyUrl}\" target=\"_blank\">Verificar mi email</a></p>
            <p>Si no creaste esta cuenta, ignora este mensaje.</p>
        ";

        $personalization = [
            new Personalization($this->notifiable->email, [
                'greeting' => "Hola {$this->notifiable->name}",
                'header_title' => 'Verifica tu correo electrónico',
                'message_intro' => 'Para completar el proceso de registro debes hacer la verificación de correo.',
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
