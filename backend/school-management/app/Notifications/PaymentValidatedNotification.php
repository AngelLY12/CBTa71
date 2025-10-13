<?php

namespace App\Notifications;

use App\Models\Payment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PaymentValidatedNotification extends Notification implements ShouldQueue
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
            ->subject('Pago validado exitosamente')
            ->greeting('Hola ' . $notifiable->name)
            ->line('Tu pago ha sido validado para el concepto: ' . $this->payment->concept->concept_name)
            ->line('Monto: $' . number_format($this->payment->paymentConcept->amount, 2))
            ->line('Tipo de método de pago: ' . $this->payment->type_payment_method)
            ->line('Código de referencia: ' . $this->payment->payment_intent_id)
            ->line('Voucher de oxxo: ' . optional($this->payment->voucher_number ?? 'No aplica'))
            ->line('Referencia SPEI: ' . optional($this->payment->spei_reference ?? 'No aplica'))
            ->line('Instrucciones del pago: ' . optional($this->payment->instructions_url ?? 'No aplica'))
            ->line('URL de comprobante: ' . $this->payment->url)
            ->line('Gracias por tu puntualidad.');
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
