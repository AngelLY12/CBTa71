<?php

namespace App\Mail;

use App\Core\Application\DTO\Request\Mail\PaymentValidatedEmailDTO;
use App\Core\Domain\Enum\Payment\PaymentStatus;
use Illuminate\Bus\Queueable;
use MailerSend\Helpers\Builder\Personalization;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use MailerSend\LaravelDriver\MailerSendTrait;

class PaymentValidatedMail extends Mailable
{
    use Queueable, SerializesModels, MailerSendTrait;

    protected PaymentValidatedEmailDTO $data;

    /**
     * Create a new message instance.
     */
    public function __construct(PaymentValidatedEmailDTO $data)
    {
        $this->data = $data;
    }

    public function build()
    {
       try {

        $voucherNumber = $this->data->payment_method_detail['oxxo']['number'] ?? 'No aplica';
        $speiReference = $this->data->payment_method_detail['spei']['reference'] ?? 'No aplica';
        $type_payment_method = $this->data->payment_method_detail['type'] ?? 'Desconocido';
        $paymentLegend = $this->buildPaymentLegend();
        $messageDetails = "
            <p><strong>Concepto:</strong> {$this->data->concept_name}</p>
            <p><strong>Monto:</strong> $".number_format($this->data->amount, 2)."</p>
            <p><strong>Monto recibido: $".number_format($this->data->amount_received, 2)." </strong></p>
            <p><strong>Método de pago:</strong> {$type_payment_method}</p>
            <p><strong>Código de referencia:</strong> {$this->data->payment_intent_id}</p>
            <p><strong>Voucher OXXO:</strong> {$voucherNumber}</p>
            <p><strong>Referencia SPEI:</strong> {$speiReference}</p>
            <p><strong>URL comprobante:</strong> <a href='{$this->data->url}' target='_blank'>{$this->data->url}</a></p>
            {$paymentLegend}
        ";

        $personalization = [
                new Personalization($this->data->recipientEmail, [
                    'greeting' => "Hola {$this->data->recipientName}",
                    'header_title' => 'Pago Validado',
                    'message_intro' => 'Tu pago ha sido validado exitosamente.',
                    'message_details' => $messageDetails,
                    'message_footer' => 'Gracias por realizar tu pago a tiempo.',
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

    private function buildPaymentLegend(): string
    {
        return match ($this->data->status) {
            PaymentStatus::UNDERPAID->value =>
                "<p style='color:#d97706;'>
                Detectamos que el monto recibido es menor al esperado.
                <br>
                <strong>Monto pendiente:</strong> $"
                . number_format(
                    $this->data->amount - $this->data->amount_received,
                    2
                ) .
                "</p>",

            PaymentStatus::OVERPAID->value =>
                "<p style='color:#059669;'>
                Tu pago tiene un <strong>monto extra</strong>.
                <br>
                <strong>Saldo extra pagado:</strong> $"
                . number_format(
                    $this->data->amount_received - $this->data->amount,
                    2
                ) .
                "</p>",

            PaymentStatus::SUCCEEDED->value => '',

            default =>
            '',
        };
    }


}
