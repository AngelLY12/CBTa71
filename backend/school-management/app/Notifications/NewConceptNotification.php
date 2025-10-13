<?php

namespace App\Notifications;

use App\Models\PaymentConcept;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewConceptNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected PaymentConcept $paymentConcept;

    /**
     * Create a new notification instance.
     */
    public function __construct(PaymentConcept $paymentConcept)
    {
        $this->paymentConcept=$paymentConcept;
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
            ->subject('Nuevo concepto de pago disponible')
            ->greeting('Hola ' . $notifiable->name)
            ->line('Se ha creado un nuevo concepto de pago para ti:')
            ->line('Concepto: ' . $this->paymentConcept->concept_name)
            ->line('Monto: $' . number_format($this->paymentConcept->amount, 2))
            ->line('Fecha límite: ' . optional($this->paymentConcept->end_date)->format('d/m/Y'))
            #->action('Notification Action', url('/'))
            ->line('Por favor realiza tu pago antes de la fecha límite.');
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
