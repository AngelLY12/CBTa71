<?php

namespace App\Mail;

use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Bus\Queueable;
use MailerSend\LaravelDriver\MailerSendTrait;
use Illuminate\Queue\SerializesModels;
use MailerSend\Helpers\Builder\Personalization;

class SendVerifyEmail extends VerifyEmail
{
    use Queueable, SerializesModels, MailerSendTrait;

    public function toMail($notifiable)
    {
        $verifyUrl = $this->verificationUrl($notifiable);

        $messageDetails = "
            <p>Para verificar tu correo, haz clic en el siguiente enlace:</p>
            <p><a href=\"{$verifyUrl}\" target=\"_blank\">Verificar mi email</a></p>
            <p>Si no creaste esta cuenta, ignora este mensaje.</p>
        ";

        $personalization = [
            new Personalization($notifiable->email, [
                'greeting' => "Hola {$notifiable->name}",
                'header_title' => 'Verifica tu correo electrónico',
                'message_intro' => 'Gracias por registrarte en nuestra plataforma.',
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
