<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RequiresActionNotification extends Notification
{
    use Queueable;

    protected array $oxxo;

    /**
     * Create a new notification instance.
     */
    public function __construct(array $oxxo)
    {
        $this->oxxo=$oxxo;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Instrucciones para completar tu pago en OXXO')
            ->greeting('Hola ' . $notifiable->name)
            ->line('Para completar tu pago, acude a cualquier tienda OXXO y presenta el código de referencia en el voucher:')
            ->line('Monto: $' . number_format($this->oxxo['amount'] / 100, 2))
            ->line('Número de referencia: ' . $this->oxxo['reference_number'])
            ->action('Ver voucher', $this->oxxo['voucher'])
            ->line('Tu pago será actualizado automáticamente una vez que completes la operación en OXXO.');

    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
