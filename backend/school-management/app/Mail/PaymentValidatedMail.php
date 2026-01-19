<?php

namespace App\Mail;

use App\Core\Application\DTO\Request\Mail\PaymentValidatedEmailDTO;
use App\Core\Domain\Enum\Payment\PaymentStatus;
use App\Core\Domain\Utils\Helpers\Money;
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
            <p><strong>Monto:</strong> $". Money::from($this->data->amount)->finalize()."</p>
            <p><strong>Monto recibido: $". Money::from($this->data->amount_received)->finalize()." </strong></p>
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
        $pending = Money::from((string) $this->data->amount)
            ->sub((string)$this->data->amount_received)
            ->finalize();
        $balance=Money::from((string) $this->data->amount_received)
            ->sub((string)$this->data->amount)
            ->finalize();
        return match ($this->data->status) {
            PaymentStatus::UNDERPAID->value =>
                "<p style='color:#d97706;'>
                Detectamos que el monto recibido es menor al esperado.
                <br>
                <strong>Monto pendiente:</strong> $"
                . $pending
                .
                "</p>",

            PaymentStatus::OVERPAID->value =>
                "<p style='color:#059669;'>
                Tu pago tiene un <strong>monto extra</strong>.
                <br>
                <strong>Saldo extra pagado:</strong> $"
                . $balance .
                "</p>",

            PaymentStatus::SUCCEEDED->value => '',

            default =>
            '',
        };
    }


}
