<?php

namespace App\Mail;

use App\Core\Application\DTO\Request\Mail\SendParentInviteEmailDTO;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use MailerSend\LaravelDriver\MailerSendTrait;
use MailerSend\Helpers\Builder\Personalization;

class SendParentInviteEmail extends Mailable
{
    use Queueable, SerializesModels, MailerSendTrait;

    /**
     * Create a new message instance.
     */
    protected SendParentInviteEmailDTO $data;

    public function __construct(SendParentInviteEmailDTO $data)
    {
        $this->data = $data;
    }


    public function build()
    {
       try {
            $acceptUrl = config('app.frontend_url') . '/parent/accept-invite?token=' . $this->data->token;

            $messageDetails = "
                <p>Has sido invitado a vincular tu cuenta como <strong>padre/madre o tutor</strong>.</p>
                <p>Para aceptar la invitación, haz clic en el siguiente enlace:</p>
                <p><a href=\"{$acceptUrl}\" target=\"_blank\">Aceptar invitación</a></p>
                <p>Si no reconoces esta invitación, puedes ignorar este mensaje.</p>
            ";

            $personalization = [
                new Personalization($this->data->recipientEmail, [
                    'greeting' => "Hola {$this->data->recipientName}",
                    'header_title' => 'Invitación de vinculación',
                    'message_intro' => 'Has recibido una invitación para vincularte como tutor.',
                    'message_details' => $messageDetails,
                    'message_footer' => 'Este enlace expirará en 48 horas por seguridad.',
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
