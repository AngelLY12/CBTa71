<?php

namespace App\Notifications;

use App\Models\Payment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PaymentCreatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected Payment $payment;

    /**
     * Create a new notification instance.
     */
    public function __construct(Payment $payment)
    {
        $this->payment=$payment;
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
            ->subject('Confirmación de pago')
            ->greeting('Hola ' . $notifiable->name)
            ->line('Hemos recibido tu pago para el concepto: ' . $this->payment->paymentConcept->concept_name)
            ->line('Monto: $' . number_format($this->payment->paymentConcept->amount, 2))
            ->line('Fecha: ' . $this->payment->created_at->format('d/m/Y H:i'))
            ->line('URL: ' . $this->payment->url ?? 'No disponible')
            ->line('Sesión del pago: ' . $this->payment->stripe_session_id ?? 'No disponible')
            ->line('Gracias por realizar tu pago a tiempo.');
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
